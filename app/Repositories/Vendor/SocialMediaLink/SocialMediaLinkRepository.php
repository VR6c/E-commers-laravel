<?php

namespace App\Repositories\Vendor\SocialMediaLink;

use App\Models\SocialMediaLink;

class SocialMediaLinkRepository implements SocialMediaLinkRepositoryInterface
{
    public function all()
    {
        return SocialMediaLink::all();
    }

    public function create(array $data)
    {
        return SocialMediaLink::create($data);
    }

    public function find($id)
    {
        return SocialMediaLink::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $link = $this->find($id);
        $link->update($data);

        return $link;
    }

    public function delete($id)
    {
        $this->find($id)->delete();
    }
}
