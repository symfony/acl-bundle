<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AclBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Acl\Dbal\Schema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\SchemaException;

/**
 * Creates the tables required by the ACL system.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
final class InitAclCommand extends Command
{
    protected static $defaultName = 'acl:init';

    private $connection;
    private $schema;

    public function __construct(Connection $connection, Schema $schema)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->schema = $schema;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Creates ACL tables in the database')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command creates ACL tables in the database.

  <info>php %command.full_name%</info>

The name of the DBAL connection must be configured in the <info>acl.connection</info> variable of your acl configuration file.

  <info>acl:
    connection: default</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->schema->addToSchema($this->connection->getSchemaManager()->createSchema());
        } catch (SchemaException $e) {
            $output->writeln('Aborting: '.$e->getMessage());

            return 1;
        }

        foreach ($this->schema->toSql($this->connection->getDatabasePlatform()) as $sql) {
            $this->connection->exec($sql);
        }

        $output->writeln('ACL tables have been initialized successfully.');

        return 0;
    }
}
