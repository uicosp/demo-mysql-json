<?php
/**
 * @author: 鱼肚 <uicosp@gmail.com>
 * @date: 2017/8/2
 */

namespace App;


class LikeService
{
    /**
     * @var UserLikeRepository
     */
    private $userLikeRepository;
    /**
     * @var ThreadRepository
     */
    private $threadRepository;

    /**
     * LikeService constructor.
     * @param UserLikeRepository $userLikeRepository
     * @param ThreadRepository $threadRepository
     */
    public function __construct(UserLikeRepository $userLikeRepository, ThreadRepository $threadRepository)
    {
        $this->userLikeRepository = $userLikeRepository;
        $this->threadRepository = $threadRepository;
    }

    public function like($uid, $tid)
    {
        $this->userLikeRepository->addLike($uid, $tid);
        $this->threadRepository->addLike($tid, $uid);
    }

    public function unlike($uid, $tid)
    {
        $this->userLikeRepository->removeLike($uid, $tid);
        $this->threadRepository->removeLike($tid, $uid);
    }
}