<?php

namespace App\Http\Controllers;

use Exception;
use getID3;
use getid3_writetags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function postInput(Request $request)
    {
        // Xóa folder tạo ra từ lần trc
        File::deleteDirectory(public_path('uploads'));

        // lưu file mp3
        if (!is_dir(public_path('uploads'))) {
            mkdir(public_path('uploads'), 0777, true);
        }
        $file_path = $request->file('fileInput')->move('uploads', 'Edit_' . $request->file('fileInput')->getClientOriginalName());
        $full_file_path = public_path($file_path);

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
            $img_path = $request->file('image')->move('uploads', 'Edit_' . $request->file('image')->getClientOriginalName());
            $full_img_path = public_path($img_path);
            if ($APICdata = file_get_contents($full_img_path)) {
                if ($exif_imagetype = exif_imagetype($full_img_path)) {
                    $TagData['attached_picture'][0]['data']          = $APICdata;
                    $TagData['attached_picture'][0]['picturetypeid'] = 0x03;
                    $TagData['attached_picture'][0]['description']   = 'Cover Art';
                    $TagData['attached_picture'][0]['mime']          = image_type_to_mime_type($exif_imagetype);
                } else {
                    dd('invalid image format (only JPG, JPEG, PNG)');
                }
            } else {
                dd('cannot open ' . htmlentities($_FILES['userfile']['tmp_name']));
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

    public function postConvert(Request $request)
    {
        // Xóa folder tạo ra từ lần trc
        File::deleteDirectory(public_path('uploads'));

        $name = 'Convert_' . pathinfo($request->file('fileInput')->getClientOriginalName(), PATHINFO_FILENAME).'.'.$request->type_format;
        $request->file('fileInput')->move('lame', 'convert.mp3');
        $cmd = 'cd ' . public_path('lame') . ' & ' . public_path('lame/lame.exe') . ' convert.mp3 converted.' . $request->type_format;
        exec($cmd, $output, $return_var);

        // Kiểm tra kết quả trả về từ lệnh
        if ($return_var === 0) {
            if (!is_dir(public_path('uploads'))) {
                mkdir(public_path('uploads'), 0777, true);
            }
            rename(public_path('lame/converted.' . $request->type_format), public_path('uploads/'.$name));
            unlink(public_path('lame/convert.mp3'));

            return response()->download(public_path('uploads/'.$name));
        } else {
            // Lệnh không thực thi thành công
            dd('Có lỗi xảy ra khi thực thi lệnh');
        }
    }
}
