<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserArchiveScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('code')->nullable();
            $table->unsignedTinyInteger('status')->nullable()->default(1);
            $table->timestamps();
        });

        DB::table('users')->insert([
            ['name' => 'Faol agent', 'code' => 'active', 'status' => 1],
            ['name' => 'Eski faol agent', 'code' => 'legacy-active', 'status' => null],
            ['name' => 'Arxiv agent', 'code' => 'archived', 'status' => 0],
        ]);
    }

    public function test_archived_users_are_hidden_from_normal_queries(): void
    {
        $this->assertSame(['Faol agent', 'Eski faol agent'], User::orderBy('id')->pluck('name')->all());
    }

    public function test_archived_users_can_be_managed_explicitly(): void
    {
        $this->assertSame(['Arxiv agent'], User::onlyArchived()->pluck('name')->all());
        $this->assertCount(3, User::withArchived()->get());
    }
}
