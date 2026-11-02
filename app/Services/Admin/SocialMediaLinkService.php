<?php

namespace App\Services\Admin;

use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepositoryInterface;

class SocialMediaLinkService
{
    protected $socialMediaLinkRepository;

    public function __construct(SocialMediaLinkRepositoryInterface $socialMediaLinkRepository)
    {
        $this->socialMediaLinkRepository = $socialMediaLinkRepository;
    }

    public function getAllSocialMediaLinks()
    {
        return $this->socialMediaLinkRepository->all();
    }

    public function createSocialMediaLink($data)
    {
        return $this->socialMediaLinkRepository->create([
            'type' => $data['type'],
            'platform' => $data['platform'],
            'link' => $data['link'],
            'name' => $data['name'] ?? ($data['languages']['en']['name'] ?? null),
        ]);
    }

    public function updateSocialMediaLink($id, $data)
    {
        return $this->socialMediaLinkRepository->update($id, [
            'type' => $data['type'],
            'platform' => $data['platform'],
            'link' => $data['link'],
            'name' => $data['name'] ?? ($data['languages']['en']['name'] ?? null),
        ]);
    }

    public function deleteSocialMediaLink($id)
    {
        $this->socialMediaLinkRepository->delete($id);
    }
}
