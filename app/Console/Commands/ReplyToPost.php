<?php

namespace App\Console\Commands;

use App\Domain\Actions\ReplyToPost as ActionsReplyToPost;
use App\Domain\LocalActor;
use App\Domain\Handle;
use App\Domain\LocalInstance;
use App\Domain\Repliable;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReplyToPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:reply {localUserHandle} {inReplyToPostAuthor} {inReplyToPostPublicId} {postBody}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reply to a post';

    protected ActionsReplyToPost $action;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ActionsReplyToPost $action)
    {
        parent::__construct();

        $this->action = $action;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $actor = $this->resolveActor();
        $inReplyToPost = $this->resolveInReplyToPost();
        $body = $this->resolvePostBody();

        $this->action->execute($actor, $inReplyToPost, $body);

        return 0;
    }

    private function resolveActor(): LocalActor
    {
        $handle = new Handle($this->argument('localUserHandle'));

        $user = User::firstWhere('handle', $handle);

        if (is_null($user)) {
            $user = new User([
                'name' => $handle,
                'handle' => $handle,
                'email' => '',
                'instance' => (new LocalInstance())->url(),
            ]);

            $user->password = Hash::make(Str::random(20));

            $user->save();
        }

        return $user->toActor();
    }

    private function resolveInReplyToPost(): Repliable
    {
        $inReplyToPostAuthor =
    }
}
