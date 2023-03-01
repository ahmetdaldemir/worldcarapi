<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Ekstra;
use App\Models\Language;
use App\Models\Location;
use App\Models\Reservation;
use App\Models\User;
use App\Services\Calculate;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;


class ApiController extends Controller
{
    /**
     * Destination for a name
     *
     */
    public function destination(Request $request)
    {
        return Destination::join('destination_languages', 'destination_languages.id_destination', '=', 'destinations.id')
            ->where('destination_languages.id_lang', $request->id)
            ->get();
    }

    public function location(Request $request)
    {
        return Location::join('location_values', 'location_values.id_location', '=', 'locations.id')
            ->where('location_values.id_lang', $request->id)
            ->select('*')
            ->get();
    }

    public function blog(Request $request)
    {
        return Blog::join('blog_languages', 'blog_languages.id_blog', '=', 'blogs.id')
            ->where('blog_languages.id_lang', $request->id)
            ->select('*')
            ->get();
    }

    public function comment()
    {
        return Comment::all();
    }

    public function language()
    {
        return Language::all();
    }

    public function staticData(): array
    {
        return $this->staticData();
    }


    /**
     * Search for a name
     *
     * @param str $name
     * @return Response
     */
    public function search(Request $request)
    {
        $calculate = new Calculate($request);
        return $calculate->index();
    }

    public function reservation_history(Request $request)
    {
        [$id, $token] = explode('|', $request->bearerToken(), 2);
        $personeTokenAccess = PersonalAccessToken::findToken($token);
        try {
            $data = Reservation::where('id_customer', $personeTokenAccess->tokenable_id)->get();
            return response()->json([
                'status' => true,
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUser(Request $request)
    {
        [$id, $token] = explode('|', $request->bearerToken(), 2);
        $personeTokenAccess = PersonalAccessToken::findToken($token);
        try {
            $data = Customer::find($personeTokenAccess->tokenable_id);
            return response()->json([
                'status' => true,
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function userUpdate(Request $request)
    {
        [$id, $token] = explode('|', $request->bearerToken(), 2);
        $personeTokenAccess = PersonalAccessToken::findToken($token);
        try {
            $data = Customer::find($personeTokenAccess->tokenable_id);
            return response()->json([
                'status' => true,
                'data' => $data,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getEkstra(Request $request)
    {
        try {
            $data = Ekstra::all();
            foreach ($data as $item) {
                $array[] = array(
                    'id' => $item->id,
                    'name' => $item->getLanguage($request->language),
                    'price' => $item->price,
                    'mandatoryInContract' => $item->mandatoryInContract,
                    'itemOfCustom' => $item->itemOfCustom,
                    'value' => $item->value,
                    'type' => $item->type,
                    'sellType' => $item->sellType,
                    'image' => "https::worldcarrental.com/".$item->image,
                );
            }
            return response()->json([
                'status' => true,
                'data' => $array,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }




}
