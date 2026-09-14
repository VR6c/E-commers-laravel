<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use App\Services\Admin\SocialMediaLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class SocialMediaLinkController extends Controller
{
    protected SocialMediaLinkService $socialMediaLinkService;

    public function __construct(SocialMediaLinkService $socialMediaLinkService)
    {
        $this->socialMediaLinkService = $socialMediaLinkService;
    }

    public function index()
    {
        $socialMediaLinks = $this->socialMediaLinkService->getAllSocialMediaLinks();

        return view('admin.social-media-links.index', compact('socialMediaLinks'));
    }

    public function getData(Request $request)
    {
        $socialMediaLinks = SocialMediaLink::query();

        return DataTables::of($socialMediaLinks)
            ->addColumn('action', function ($socialMediaLink) {
                return '<div class="dt-actions">
                    <a href="'.route('admin.social-media-links.edit', $socialMediaLink->id).'" class="btn-action btn-action-edit" title="Edit">
                        <i class="bi bi-pencil-fill"></i>
                    </a>
                    <button type="button" class="btn-action btn-action-delete" onclick="deleteLink('.$socialMediaLink->id.')" title="Delete">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    // Other controller methods...

    public function create()
    {
        return view('admin.social-media-links.create');
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

        return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link created successfully.');
    }

    public function edit($id)
    {
        $socialMediaLink = SocialMediaLink::findOrFail($id);

        return view('admin.social-media-links.edit', compact('socialMediaLink'));
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

        return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $socialMediaLink = SocialMediaLink::findOrFail($id);
            $socialMediaLink->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Social media link deleted successfully.',
                ]);
            }

            return redirect()->route('admin.social-media-links.index')->with('success', 'Social media link deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting social media link with ID {$id}: " . $e->getMessage());

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the social media link.',
                ]);
            }

            return redirect()->route('admin.social-media-links.index')->with('error', 'An error occurred while deleting the social media link.');
        }
    }
}
