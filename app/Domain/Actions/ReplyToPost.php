<?php

namespace App\Domain\Actions;

use App\Domain\Post;

class ReplyToPost
{
    protected SendReplyToFederatedInstance $sendReplyToFederatedInstanceAction;

    public function __construct(SendReplyToFederatedInstance $sendReplyToFederatedInstanceAction)
    {
        $this->sendReplyToFederatedInstanceAction = $sendReplyToFederatedInstanceAction;
    }

    public function execute(Post $reply): Post
    {
        $this->sendReplyToFederatedInstanceAction->execute($reply);

        return $reply;
    }
}
