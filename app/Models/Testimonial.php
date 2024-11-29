<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManagerStatic as Image;

class Testimonial extends Model
{
    use CrudTrait;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ["id"];

    public function setAvatarUrlAttribute($value)
    {
        $attribute_name = "avatar_url";
        // or use your own disk, defined in config/filesystems.php
        $disk = "s3";
        // destination path relative to the disk above
        $destination_path = "testimonials/avatars";

        // if the image was erased
        if (empty($value)) {
            // delete the image from disk
            if (
                isset($this->{$attribute_name}) &&
                !empty($this->{$attribute_name})
            ) {
                \Storage::disk($disk)->delete($this->{$attribute_name});
            }
            // set null on database column
            $this->attributes[$attribute_name] = null;
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, "data:image")) {
            // 0. Make the image

            $manager = new ImageManager(Driver::class);
            $image = $manager->read($value);
            $image = $image
                ->resize(400, 400)
                ->encode(new WebpEncoder(quality: 65));

            // $image = Image::make($value)
            //     ->encode("webp", 80)
            //     ->resize(400, 400, function ($constraint) {
            //         $constraint->aspectRatio();
            //         $constraint->upsize();
            //     });

            // 1. Generate a filename.
            $filename = md5($value . time()) . ".webp";

            // 2. Store the image on disk.
            \Storage::disk($disk)->put(
                $destination_path . "/" . $filename,
                $image->toFilePointer()
            );

            // 3. Delete the previous image, if there was one.
            if (
                isset($this->{$attribute_name}) &&
                !empty($this->{$attribute_name})
            ) {
                \Storage::disk($disk)->delete($this->{$attribute_name});
            }

            // 4. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it
            // from the root folder; that way, what gets saved in the db
            // is the public URL (everything that comes after the domain name)
            // $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            // $this->attributes[$attribute_name] = $public_destination_path . '/' . $filename;
            $this->attributes[$attribute_name] = \Storage::url(
                $destination_path . "/" . $filename
            );
        } elseif (!empty($value)) {
            // if value isn't empty, but it's not an image, assume it's the model value for that attribute.
            $this->attributes[$attribute_name] = $this->{$attribute_name};
        }
    }
}
