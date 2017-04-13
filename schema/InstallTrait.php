<?php

namespace go1\util\schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use go1\kv\KV;
use go1\util\DB;
use go1\util\plan\PlanRepository;

trait InstallTrait
{
    public function installGo1Schema(Connection $db, $coreOnly = true)
    {
        DB::install($db, [
            function (Schema $schema) {
                if (!$schema->hasTable('gc_kv')) {
                    if (class_exists(KV::class)) {
                        KV::migrate($schema, 'gc_kv');
                    }
                }

                if (!$schema->hasTable('gc_ro')) {
                    $edge = $schema->createTable('gc_ro');
                    $edge->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
                    $edge->addColumn('type', 'integer', ['unsigned' => true]);
                    $edge->addColumn('source_id', 'integer', ['unsigned' => true]);
                    $edge->addColumn('target_id', 'integer', ['unsigned' => true]);
                    $edge->addColumn('weight', 'integer', ['unsigned' => true]);
                    $edge->addColumn('data', 'text', ['notnull' => false]);
                    $edge->setPrimaryKey(['id']);
                    $edge->addIndex(['source_id']);
                    $edge->addIndex(['target_id']);
                    $edge->addUniqueIndex(['type', 'source_id', 'target_id']);
                }

                PortalSchema::install($schema);
                UserSchema::install($schema);
                LoSchema::install($schema);
                EnrolmentSchema::install($schema);
                PlanRepository::install($schema);
            },
            function (Schema $schema) use ($coreOnly) {
                if (!$coreOnly) {
                    SocialSchema::install($schema);
                    NoteSchema::install($schema);
                    VoteSchema::install($schema);
                }
            },
        ]);
    }
}
