<?php

namespace App\Console\Commands;

use App\Domain\Actions\ReplyToPost as ActionsReplyToPost;
use App\Domain\LocalActor;
use App\Domain\Handle;
use App\Domain\LocalInstance;
use App\Domain\PostBody;
use App\Domain\RemoteActor;
use App\Domain\RemotePost;
use App\Domain\Repliable;
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
    protected $signature = 'post:reply {localUserUsername} {inReplyToPostAuthor} {inReplyToPostPublicId} {postBody}';

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
        $username = $this->argument('localUserUsername');;

        $user = User::firstWhere('username', $username);

        if (is_null($user)) {
            $user = new User([
                'name' => $username,
                'username' => $username,
                'email' => '',
                'instance' => (new LocalInstance())->url(),
            ]);

            $user->password = Hash::make(Str::random(20));

            $user->save();
        }

        return $user->toDomainObject();
    }

    private function resolveInReplyToPost(): Repliable
    {
        return new RemotePost($this->resolveInReplyToPostAuthor(), $this->argument('inReplyToPostPublicId'));
    }

    private function resolveInReplyToPostAuthor(): RemoteActor
    {
        return new RemoteActor(Handle::fromString($this->argument('inReplyToPostAuthor')));
    }

    private function resolvePostBody(): PostBody
    {
        return new PostBody($this->argument('postBody'));
    }
}
