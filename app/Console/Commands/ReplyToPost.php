<?php

namespace App\Console\Commands;

use App\Domain\Actions\ReplyToPost as ActionsReplyToPost;
use App\Domain\LocalActor;
use App\Domain\Handle;
use App\Domain\LocalInstance;
use App\Domain\Post;
use App\Domain\PostBody;
use App\Domain\RemoteActor;
use App\Domain\RemotePost;
use App\Domain\Repliable;
use App\Models\Instance;
use App\Models\Post as ModelsPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

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
        $inReplyToPostAuthor = $this->resolveInReplyToPostAuthor();
        $inReplyToPost = $this->resolveInReplyToPost();
        $body = $this->resolvePostBody();
        $reply = new Post($actor, $body, Uuid::uuid4(), Carbon::now(), $inReplyToPost);

        $originalPostInstance = Instance::firstOrCreate([
            'url' => $inReplyToPostAuthor->instance()->url(),
        ]);

        $originalPostAuthor = User::firstOrNew([
            'username' => $inReplyToPostAuthor->handle()->username(),
            'instance_id' => $originalPostInstance->id,
        ], [
            'name' => $inReplyToPostAuthor->handle()->username(),
            'email' => (string) $inReplyToPostAuthor->handle(),
        ]);
        $originalPostAuthor->password = Str::random(16);
        $originalPostAuthor->save();

        $originalPost = ModelsPost::firstOrCreate([
            'user_id' => $originalPostAuthor->id,
            'public_id' => $inReplyToPost->publicId(),
            'body' => $body,
        ]);

        $savedPost = new ModelsPost([
            'user_id' => $this->resolveUser()->id,
            'public_id' => $reply->publicId(),
            'reply_to_post_id' => $originalPost->id,
            'body' => $body,
        ]);
        $savedPost->save();

        $this->action->execute($reply);

        return 0;
    }

    private function resolveUser(): User
    {
        $username = $this->argument('localUserUsername');;

        $user = User::firstWhere('username', $username);

        if (is_null($user)) {
            $user = new User([
                'name' => $username,
                'username' => $username,
                'email' => $username . '@' . str_replace('http://', '', str_replace('https://', '', url(''))),
            ]);

            $user->password = Hash::make(Str::random(20));

            $user->save();
        }

        return $user;
    }

    private function resolveActor(): LocalActor
    {
        return $this->resolveUser()->toDomainObject();
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
