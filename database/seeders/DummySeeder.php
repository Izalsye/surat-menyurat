<?php

namespace Database\Seeders;

use App\Enum\Permission;
use App\Models\Disposition;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $total = 21_536;

        $user = User::query()->value('id');
        $assignee = User::query()
            ->whereNot('id', $user)
            ->whereHas('roles.permissions', function ($query) {
                $query->where('name', Permission::ViewDisposition);
            })->value('id');

        echo $total . ' incoming letter seeding...' . PHP_EOL;
        IncomingLetter::factory()
            ->count($total)
            ->create([
                'created_by' => $user,
            ]);

        $outGoingTotal = $total - fake()->numberBetween(floor($total / 8), $total - 1);
        echo $outGoingTotal . ' outgoing letter seeding...' . PHP_EOL;
        OutgoingLetter::factory()
            ->count($outGoingTotal)
            ->create([
                'created_by' => $user,
            ]);

        $dispositionTotal = fake()->numberBetween(floor($total / 2), $total - 1);
        echo $dispositionTotal . ' disposition seeding...' . PHP_EOL;
        $letters = IncomingLetter::query()
            ->whereDoesntHave('dispositions')
            ->take($dispositionTotal)
            ->get();
        foreach ($letters as $letter) {
            $createdAt = fake()->dateTimeBetween($letter->created_at);
            $replyLetter = fake()->boolean(15);

            $dueAt = $replyLetter ? fake()->dateTimeBetween($createdAt, '2 weeks') : null;

            $isDone = fake()->boolean($replyLetter ? 0 : 75);
            $doneAt = $isDone ? now() : null;

            Disposition::factory()->state([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
                'reply_letter' => $replyLetter,
                'is_done' => $isDone,
                'done_at' => $doneAt,
                'due_at' => $dueAt,
            ])->create([
                'assigner_id' => $user,
                'assignee_id' => $assignee,
                'incoming_letter_id' => $letter->id,
            ]);
        }

        $dispositions = Disposition::query()
            ->where('reply_letter', true)
            ->where('is_done', false)
            ->get();
        $count = $dispositions->count();
        $take = fake()->numberBetween(1, $count-1);
        echo "Replying to $take of $count letters..." . PHP_EOL;
        foreach ($dispositions->take($take) as $disposition) {
            $outgoing = OutgoingLetter::factory()->forDisposition($disposition)->create();
            $disposition->update([
                'is_done' => true,
                'done_at' => $outgoing->created_at,
            ]);
        }

        echo 'Done.' . PHP_EOL;
    }
}
