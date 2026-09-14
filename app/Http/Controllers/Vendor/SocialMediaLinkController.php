<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use App\Services\Vendor\SocialMediaLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class SocialMediaLinkController extends Controller
{
    protected SocialMediaLinkService $socialMediaLinkService;

    public function __construct(SocialMediaLinkService $socialMediaLinkService)
    {
        $this->middleware('auth:vendor');
        $this->socialMediaLinkService = $socialMediaLinkService;
    }

    public function index()
    {
        $socialMediaLinks = $this->socialMediaLinkService->getAllSocialMediaLinks();

        return view('vendor.social-media-links.index', compact('socialMediaLinks'));
    }

    public function getData(Request $request)
    {
        $socialMediaLinks = SocialMediaLink::query();

        return DataTables::of($socialMediaLinks)
            ->addColumn('action', function ($link) {
                return '
                    <div class="dt-actions">
                        <a href="' . route('vendor.social-media-links.edit', $link->id) . '"
                           class="btn-action btn-action-edit" title="Edit">
                            <i class="bi bi-pencil-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteLink(' . $link->id . ')" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('vendor.social-media-links.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'     => 'required|in:facebook,instagram,tiktok,youtube,x',
            'platform' => 'required|string|max:255',
            'link'     => 'required|url',
            'name'     => 'required|string|max:255',
        ]);

        $this->socialMediaLinkService->createSocialMediaLink($request->all());

        return redirect()
            ->route('vendor.social-media-links.index')
            ->with('success', 'Social media link created successfully.');
    }

    public function edit($id)
    {
        $socialMediaLink = $this->socialMediaLinkService->getAllSocialMediaLinks()->find($id);

        return view('vendor.social-media-links.edit', compact('socialMediaLink'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'type'     => 'required|in:facebook,instagram,tiktok,youtube,x',
            'platform' => 'required|string|max:255',
            'link'     => 'required|url',
            'name'     => 'required|string|max:255',
        ]);

        $this->socialMediaLinkService->updateSocialMediaLink($id, $request->all());

        return redirect()
            ->route('vendor.social-media-links.index')
            ->with('success', 'Social media link updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $this->socialMediaLinkService->deleteSocialMediaLink($id);

            return response()->json([
                'success' => true,
                'message' => 'Social media link deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Vendor: error deleting social media link ID {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the social media link.',
            ]);
        }
    }
}
