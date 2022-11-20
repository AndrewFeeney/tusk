<?php

namespace App\Domain\Actions;

use App\Domain\Actor;
use App\Domain\DraftReply;
use App\Domain\Post;
use App\Domain\PostBody;
use App\Domain\Repliable;

class ReplyToPost
{
    protected SendReplyToFederatedInstance $sendReplyToFederatedInstanceAction;

    public function __construct(SendReplyToFederatedInstance $sendReplyToFederatedInstanceAction)
    {
        $this->sendReplyToFederatedInstanceAction = $sendReplyToFederatedInstanceAction;
    }

    public function execute(Actor $actor, Repliable $inReplyToPost, PostBody $body): Post
    {
        $draftReply = new DraftReply($actor, $inReplyToPost, $body);

        $this->sendReplyToFederatedInstanceAction->execute($draftReply);

        return $draftReply->toPost();
    }
}
