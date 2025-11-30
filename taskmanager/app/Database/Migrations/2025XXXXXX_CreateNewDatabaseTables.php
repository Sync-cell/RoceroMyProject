<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class CreateNewDatabaseTables extends Migration
{
    public function up()
    {
        $db = Database::connect();

        // ------------------------------
        // Admins Table
        // ------------------------------
        if (! $db->tableExists('admins')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'unique'     => true,
                ],
                'password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('admins', true);
        }

        // ------------------------------
        // Users Table
        // ------------------------------
        if (! $db->tableExists('users')) {
            $this->forge->addField([
                'user_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'full_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'unique'     => true,
                ],
                'password' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'role' => [
                    'type'       => 'ENUM',
                    'constraint' => ['admin', 'user'],
                    'default'    => 'user',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active','inactive'],
                    'default'    => 'active',
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('user_id', true);
            $this->forge->createTable('users', true);
        }

        // ------------------------------
        // Tasks Table
        // ------------------------------
        if (! $db->tableExists('tasks')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'priority' => [
                    'type'       => 'ENUM',
                    'constraint' => ['low','medium','high'],
                ],
                'deadline' => [
                    'type' => 'DATE',
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending','in_progress','completed'],
                    'default'    => 'pending',
                ],
                'assigned_to' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('tasks', true);
        }

        // ------------------------------
        // Vehicles Table
        // ------------------------------
        if (! $db->tableExists('vehicles')) {
            $this->forge->addField([
                'vehicle_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'brand' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'model' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'plate_number' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'rental_price' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'availability_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['available','unavailable'],
                    'default'    => 'available',
                ],
            ]);
            $this->forge->addKey('vehicle_id', true);
            $this->forge->createTable('vehicles', true);
        }

        // ------------------------------
        // Bookings Table
        // ------------------------------
        if (! $db->tableExists('bookings')) {
            $this->forge->addField([
                'booking_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'vehicle_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'purpose' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'rental_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'return_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'booking_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'pending',
                ],
            ]);
            $this->forge->addKey('booking_id', true);
            $this->forge->addKey('user_id');
            $this->forge->addKey('vehicle_id');
            $this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
            $this->forge->addForeignKey('vehicle_id','vehicles','vehicle_id','CASCADE','CASCADE');
            $this->forge->createTable('bookings', true);
        }

        // ------------------------------
        // Payments Table
        // ------------------------------
        if (! $db->tableExists('payments')) {
            $this->forge->addField([
                'payment_id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'booking_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'amount' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'null'       => true,
                ],
                'payment_method' => [
                    'type'       => 'ENUM',
                    'constraint' => ['cash','card','online'],
                    'null'       => true,
                ],
                'proof_of_payment' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'payment_status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending','paid','failed'],
                    'default'    => 'pending',
                ],
                'transaction_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('payment_id', true);
            $this->forge->addKey('booking_id');
            $this->forge->addForeignKey('booking_id','bookings','booking_id','CASCADE','CASCADE');
            $this->forge->createTable('payments', true);
        }

        // ------------------------------
        // Uploads Table
        // ------------------------------
        if (! $db->tableExists('uploads')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'file_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'file_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'uploaded_at' => [
                    'type'    => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('user_id');
            $this->forge->addForeignKey('user_id','users','user_id','CASCADE','CASCADE');
            $this->forge->createTable('uploads', true);
        }

        // ------------------------------
        // IP Monitor Table
        // ------------------------------
        if (! $db->tableExists('ip_monitor')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => false,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'hits' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'first_seen' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'last_seen' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'blocked' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'blocked_by' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'blocked_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'blocked_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('ip_address');
            $this->forge->createTable('ip_monitor', true);
        }

        // ------------------------------
        // Activity Logs Table
        // ------------------------------
        if (! $db->tableExists('activity_logs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
                'username' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'action' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'details' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 45,
                    'null'       => true,
                ],
                'mac_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'default' => 'CURRENT_TIMESTAMP',
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('activity_logs', true);
        }
    }

    public function down()
    {
        $db = Database::connect();

        // Drop activity_logs and ip_monitor first (dependents)
        if ($db->tableExists('activity_logs')) {
            $this->forge->dropTable('activity_logs', true);
        }
        if ($db->tableExists('ip_monitor')) {
            $this->forge->dropTable('ip_monitor', true);
        }

        // Then drop other tables if they exist
        $tables = ['uploads','payments','bookings','vehicles','tasks','users','admins'];
        foreach ($tables as $t) {
            if ($db->tableExists($t)) {
                $this->forge->dropTable($t, true);
            }
        }
    }
}
