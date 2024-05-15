<?php

namespace App\Http\Controllers;

use getID3;
use getid3_writetags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function postInput(Request $request)
    {
        // Xóa folder tạo ra từ lần trc
        File::deleteDirectory(storage_path('app/temp'));

        // lưu file mp3
        $file_path = $request->file('fileInput')->storeAs('temp', 'Edit_' . $request->file('fileInput')->getClientOriginalName());
        $full_file_path = storage_path('app/' . $file_path);

        // khởi tạo trình đọc ghi metadata
        $TextEncoding = 'UTF-8';
        $getID3 = new getID3();
        $getID3->setOption(['encoding' => $TextEncoding]);

        $tagwriter = new getid3_writetags();
        $tagwriter->filename = $full_file_path;
        $tagwriter->tagformats = ['id3v2.3'];
        $tagwriter->overwrite_tags = true;
        $tagwriter->remove_other_tags = false;
        $tagwriter->tag_encoding = $TextEncoding;

        // gán thông tin từ request
        $TagData = [
            'album' => [$request->album],
            'artist' => [$request->artist],
            'genre' => [$request->genre],
            'title' => [$request->title],
        ];

        // riêng ảnh ktra có truyền trong request thì thay ảnh
        if ($request->file('image')) {
            $img_path = $request->file('image')->storeAs('temp', 'Edit_' . $request->file('image')->getClientOriginalName());
            $full_img_path = storage_path('app/' . $img_path);
            if ($APICdata = file_get_contents($full_img_path)) {
                if ($exif_imagetype = exif_imagetype($full_img_path)) {
                    $TagData['attached_picture'][0]['data']          = $APICdata;
                    $TagData['attached_picture'][0]['picturetypeid'] = 0x03;
                    $TagData['attached_picture'][0]['description']   = 'Cover Art';
                    $TagData['attached_picture'][0]['mime']          = image_type_to_mime_type($exif_imagetype);
                } else {
                    dd('invalid image format (only GIF, JPEG, PNG)');
                }
            } else {
                dd('cannot open '.htmlentities($_FILES['userfile']['tmp_name']));
            }
        }
        
        // lưu
        $tagwriter->tag_data = $TagData;
        if ($tagwriter->WriteTags()) {
            return response()->download($full_file_path);
        } else {
            dd('Failed to write tags!');
        }
    }
}
