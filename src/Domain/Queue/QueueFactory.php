<?php
/**
 * @author: AlexK
 * Date: 16-Jan-19
 * Time: 12:14 AM
 */

namespace ForgottenBooks\Domain\Queue;


class QueueFactory
{
    private $auth;

    public function __construct($auth)
    {
        $this->auth = $auth;
    }

    /**
     * Queue # 0
     */
    public function upload()
    {
        return new QueueUploader($this->auth);
    }

    /**
     * Queue # 1
     */
    public function proofreader()
    {
        return new QueueProcessor(1, 'Queue/Proofreader.html', 'ACTIVE_PROOFREADER', '/queue/proofreader', '1&rBarr;Proofreader', $this->auth);
    }

    /**
     * Queue # 2
     */
    public function proofing_supervisor()
    {
        return new QueueProcessor(2, 'Queue/ProofingSupervisor.html', 'ACTIVE_PROOFREADER_SUPERVISOR', '/queue/proofing_supervisor', '2&rBarr;Proofing Supervisor', $this->auth);
    }

    /**
     * Queue # 3
     */
    public function image_editor()
    {
        return new QueueProcessor(3, 'Queue/ImageEditor.html', 'ACTIVE_IMAGE_EDITOR', '/queue/image_editor', '3&rBarr;Image Editor', $this->auth);
    }

    /**
     * Queue # 4
     */
    public function layout_editor()
    {
        return new QueueProcessor(4, 'Queue/LayoutEditor.html', 'ACTIVE_LAYOUT', '/queue/layout_editor', '4&rBarr;Layout Editor', $this->auth);
    }

    /**
     * Queue # 5
     */
    public function layout_supervisor()
    {
        return new QueueProcessor(5, 'Queue/LayoutSupervisor.html', 'ACTIVE_LAYOUT_SUPERVISOR', '/queue/layout_supervisor', '5&rBarr;Layout Supervisor', $this->auth);
    }

    /**
     * Queue # 6
     */
    public function blurb_writer()
    {
        return new QueueProcessor(6, 'Queue/BlurbWriter.html', 'ACTIVE_BLURB_WRITER', '/queue/blurb_writer', '6&rBarr;Blurb Writer', $this->auth);
    }

    /**
     * Queue # 7
     */
    public function blurb_editor()
    {
        return new QueueProcessor(7, 'Queue/BlurbEditor.html', 'ACTIVE_BLURB_EDITOR', '/queue/blurb_editor', '7&rBarr;Blurb Editor', $this->auth);
    }

    /**
     * Queue # 8
     */
    public function cover_artist()
    {
        return new QueueProcessor(8, 'Queue/CoverArtist.html', 'ACTIVE_COVER_ARTIST', '/queue/cover_artist', '8&rBarr;Cover Artist', $this->auth);
    }

    /**
     * Queue # 9
     */
    public function coordinator()
    {
        return new QueueProcessor(9, 'Queue/Coordinator.html', 'ACTIVE_FINAL_APPROVAL', '/queue/coordinator', '9&rBarr;Final Approval', $this->auth);
    }
}