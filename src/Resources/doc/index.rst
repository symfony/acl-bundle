How to Use Access Control Lists (ACLs)
======================================

In complex applications, you will often face the problem that access decisions
cannot only be based on the person (``Token``) who is requesting access, but
also involve a domain object that access is being requested for. This is where
the ACL system comes in.

Using ACL's isn't trivial, and for simpler use cases, it may be overkill. If
your permission logic could be described by just writing some code (e.g. to
check if a Blog is owned by the current User), then consider using Symfony
`built-in security voters`_.

A voter is passed the object being voted on, which you can use to make complex
decisions and effectively implement your own ACL. Enforcing authorization (e.g.
the ``isGranted()`` part) will look similar to what you see in this entry, but
your voter class will handle the logic behind the scenes, instead of the ACL
system.

Imagine you are designing a blog system where your users can comment on your
posts. Now, you want a user to be able to edit their own comments, but not those
of other users; besides, you want to be able to edit all comments. In this
scenario, ``Comment`` would be the domain object that you want to restrict
access to. You could take several approaches to accomplish this using Symfony,
two basic approaches are (non-exhaustive):

- *Enforce security in your business methods*: Basically, that means keeping a
  reference inside each ``Comment`` to all users who have access, and then
  compare these users to the provided ``Token``.
- *Enforce security with roles*: In this approach, you would add a role for
  each ``Comment`` object, i.e. ``ROLE_COMMENT_1``, ``ROLE_COMMENT_2``, etc.

Both approaches are perfectly valid. However, they couple your authorization
logic to your business code which makes it less reusable elsewhere, and also
increases the difficulty of unit testing. Besides, you could run into
performance issues if many users would have access to a single domain object.

Fortunately, there is a better way, which you will find out about now.

Bootstrapping
-------------

Now, before you can finally get into action, you need to do some bootstrapping.
First, you need to configure (using YAML, XML or PHP) the connection the ACL
system is supposed to use:

.. code-block:: yaml

    # app/config/security.yml
    security:
        # ...

        acl:
            connection: default

.. code-block:: xml

    <!-- app/config/security.xml -->
    <?xml version="1.0" encoding="UTF-8"?>
    <srv:container xmlns="http://symfony.com/schema/dic/security"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:srv="http://symfony.com/schema/dic/services"
        xsi:schemaLocation="http://symfony.com/schema/dic/services
            http://symfony.com/schema/dic/services/services-1.0.xsd">

        <config>
            <!-- ... -->

            <acl connection="default" />
        </config>
    </srv:container>

.. code-block:: php

    // app/config/security.php
    $container->loadFromExtension('security', array(
        // ...

        'acl' => array(
            'connection' => 'default',
        ),
    ));

.. note::

    The ACL system requires a connection from either Doctrine DBAL (usable by
    default) or Doctrine MongoDB (usable with `MongoDBAclBundle`_). However,
    that does not mean that you have to use Doctrine ORM or ODM for mapping your
    domain objects. You can use whatever mapper you like for your objects, be it
    Doctrine ORM, MongoDB ODM, Propel, raw SQL, etc. The choice is yours.

After the connection is configured, you have to import the database structure
running the following command:

.. code-block:: terminal

    $ php bin/console acl:init

If you are using `DoctrineMigrationsBundle`_, the schema changes can be applied
by diffing your current schema.

.. code-block:: terminal

    $ php bin/console doctrine:migration:diff

This will create a new migration you can then apply.

.. code-block:: terminal

    $ php bin/console doctrine:migration:migrate

Getting Started
---------------

Coming back to the small example from the beginning, you can now implement
ACL for it.

Once the ACL is created, you can grant access to objects by creating an
Access Control Entry (ACE) to solidify the relationship between the entity
and your user.

Creating an ACL and Adding an ACE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    // src/AppBundle/Controller/BlogController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Security\Core\Exception\AccessDeniedException;
    use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
    use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
    use Symfony\Component\Security\Acl\Permission\MaskBuilder;

    class BlogController extends Controller
    {
        // ...

        public function addCommentAction(Post $post)
        {
            $comment = new Comment();

            // ... setup $form, and submit data

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($comment);
                $entityManager->flush();

                // creating the ACL
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = ObjectIdentity::fromDomainObject($comment);
                $acl = $aclProvider->createAcl($objectIdentity);

                // retrieving the security identity of the currently logged-in user
                $tokenStorage = $this->get('security.token_storage');
                $user = $tokenStorage->getToken()->getUser();
                $securityIdentity = UserSecurityIdentity::fromAccount($user);

                // grant owner access
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                $aclProvider->updateAcl($acl);
            }
        }
    }

There are a couple of important implementation decisions in this code snippet.
For now, I only want to highlight two:

First, you may have noticed that ``->createAcl()`` does not accept domain
objects directly, but only implementations of the ``ObjectIdentityInterface``.
This additional step of indirection allows you to work with ACLs even when you
have no actual domain object instance at hand. This will be extremely helpful
if you want to check permissions for a large number of objects without
actually hydrating these objects.

The other interesting part is the ``->insertObjectAce()`` call. In the
example, you are granting the user who is currently logged in owner access to
the Comment. The ``MaskBuilder::MASK_OWNER`` is a pre-defined integer bitmask;
don't worry the mask builder will abstract away most of the technical details,
but using this technique you can store many different permissions in one
database row which gives a considerable boost in performance.

.. tip::

    The order in which ACEs are checked is significant. As a general rule, you
    should place more specific entries at the beginning.

Checking Access
~~~~~~~~~~~~~~~

.. code-block:: php

    // src/AppBundle/Controller/BlogController.php

    // ...

    class BlogController
    {
        // ...

        public function editCommentAction(Comment $comment)
        {
            $authorizationChecker = $this->get('security.authorization_checker');

            // check for edit access
            if (false === $authorizationChecker->isGranted('EDIT', $comment)) {
                throw new AccessDeniedException();
            }

            // ... retrieve actual comment object, and do your editing here
        }
    }

In this example, you check whether the user has the ``EDIT`` permission.
Internally, Symfony maps the permission to several integer bitmasks, and
checks whether the user has any of them.

.. note::

    You can define up to 32 base permissions (depending on your OS PHP might
    vary between 30 to 32). In addition, you can also define cumulative
    permissions.

Cumulative Permissions
----------------------

In the first example above, you only granted the user the ``OWNER`` base
permission. While this effectively also allows the user to perform any
operation such as view, edit, etc. on the domain object, there are cases where
you may want to grant these permissions explicitly.

The ``MaskBuilder`` can be used for creating bit masks easily by combining
several base permissions:

.. code-block:: php

    $builder = new MaskBuilder();
    $builder
        ->add('view')
        ->add('edit')
        ->add('delete')
        ->add('undelete')
    ;
    $mask = $builder->get(); // int(29)

This integer bitmask can then be used to grant a user the base permissions you
added above:

.. code-block:: php

    $identity = new UserSecurityIdentity('johannes', 'AppBundle\Entity\User');
    $acl->insertObjectAce($identity, $mask);

The user is now allowed to view, edit, delete, and un-delete objects.

.. _`built-in security voters`: https://symfony.com/doc/current/security/voters.html
.. _`DoctrineMigrationsBundle`: https://symfony.com/doc/master/bundles/DoctrineMigrationsBundle/index.html
.. _`MongoDBAclBundle`: https://github.com/IamPersistent/MongoDBAclBundle
