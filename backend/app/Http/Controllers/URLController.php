<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\URL;  
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use OpenApi\Annotations as OA;

class URLController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/shorten",
     *     summary="Shorten a URL",
     *     description="This endpoint allows the user to shorten a URL. The user needs to provide a valid URL and will receive a shortened version.",
     *     operationId="shortenUrl",
     *     tags={"URLs"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="URL to be shortened",
     *         @OA\JsonContent(
     *             required={"url"},
     *             @OA\Property(property="url", type="string", format="uri", example="https://www.example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="URL shortened successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="short_url", type="string", example="http://localhost:8000/abc12345")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid.")
     *         )
     *     )
     * )
     */
    public function shorten(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        $shortCode = Str::random(8);
        while (URL::where('short_code', $shortCode)->exists()) {
            $shortCode = Str::random(8);
        }

        $url = URL::create([
            'original_url' => $request->url,
            'short_code' => $shortCode,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['short_url' => url($shortCode)], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/urls",
     *     summary="Retrieve all URLs for the authenticated user",
     *     description="This endpoint returns a list of all URLs shortened by the authenticated user.",
     *     operationId="getUserUrls",
     *     tags={"URLs"},
     *     @OA\Response(
     *         response=200,
     *         description="List of URLs created by the authenticated user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="original_url", type="string", example="https://www.example.com"),
     *                 @OA\Property(property="short_code", type="string", example="abc12345"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-25T10:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-25T10:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function urls(Request $request)
    {
        $userId = auth()->id();
        $urls = URL::where('user_id', $userId)->get();

        return response()->json($urls);
    }

    /**
     * @OA\Get(
     *     path="/api/redirect/{shortCode}",
     *     summary="Redirect to the original URL",
     *     description="This endpoint allows the user to be redirected to the original URL by using the shortened URL code.",
     *     operationId="redirectToOriginalUrl",
     *     tags={"URLs"},
     *     @OA\Parameter(
     *         name="shortCode",
     *         in="path",
     *         required=true,
     *         description="The shortened URL code",
     *         @OA\Schema(type="string", example="abc12345")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirecting to the original URL"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shortened URL not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Short URL not found")
     *         )
     *     )
     * )
     */
    public function redirect($shortCode)
    {
        $url = URL::where('short_code', $shortCode)->firstOrFail();
        return redirect($url->original_url);
    }

    /**
     * @OA\Delete(
     *     path="/api/urls/{id}",
     *     summary="Delete a URL",
     *     description="This endpoint allows the authenticated user to delete a previously shortened URL.",
     *     operationId="deleteUrl",
     *     tags={"URLs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the URL to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="URL deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="URL deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - User is not the owner of the URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="URL not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="URL not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="An error occurred while deleting the URL.")
     *         )
     *     )
     * )
     */
    public function destroy($id)
	{
	    // Retrieve the URL by ID
	    $url = URL::find($id);

	    // Check if the URL exists
	    if (!$url) {
	        return response()->json(['message' => 'URL not found'], 404);
	    }

	    // Check if the authenticated user owns the URL
	    if ($url->user_id !== auth()->id()) {
	        return response()->json(['message' => 'Unauthorized'], 404);
	    }

	    // Delete the URL
	    try {
	        $url->delete();
	        return response()->json(['message' => 'URL deleted successfully'], 200);
	    } catch (\Exception $e) {
	        return response()->json(['message' => 'An error occurred while deleting the URL.', 'error' => $e->getMessage()], 500);
	    }
	}
}
