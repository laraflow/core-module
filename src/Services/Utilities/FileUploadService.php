<?php

namespace Laraflow\Core\Services\Utilities;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Laraflow\Core\Abstracts\Service\Service;
use Laravolt\Avatar\Facade as Avatar;
use function public_path;

class FileUploadService extends Service
{
    /**
     * @param string $name
     * @param string $extension
     * @return string|null
     *
     * @throws Exception
     */
    public function avatarImageFromText(string $name, string $extension = 'jpg'): ?string
    {
        $fileName = $this->randomFileName($extension);

        $tmpPath = public_path('/media/tmp/');

        if (!is_dir($tmpPath)) {
            mkdir($tmpPath, '0777', true);
        }

        $imageObject = Image::canvas(256, 256, '#ffffff');

        try {
            $imageObject = Avatar::create($name)->getImageObject();
        } catch (Exception $imageMakeException) {
            $imageObject = Image::make(config('constant.user_profile_image'));
            Log::error($imageMakeException->getMessage());
        } finally {
            try {
                if ($imageObject instanceof \Intervention\Image\Image) {
                    if ($imageObject->resize(256, 256)->save($tmpPath . $fileName, 80, $extension)) {
                        return $tmpPath . $fileName;
                    } else {
                        return null;
                    }
                }
            } catch (Exception $imageSaveException) {
                Log::error($imageSaveException->getMessage());

                return null;
            }
        }

        return null;
    }

    /**
     * @param string $extension
     * @return string
     */
    private function randomFileName(string $extension = 'jpg'): string
    {
        return Str::random(32) . '.' . $extension;
    }

    /**
     * @param UploadedFile $file
     * @param string $extension
     * @return string|null
     */
    public function avatarImageFromInput(UploadedFile $file, string $extension = 'jpg'): ?string
    {
        $fileName = $this->randomFileName($extension);
        $tmpPath = public_path('/media/tmp/');
        $imageObject = Image::canvas(256, 256, '#ffffff');

        try {
            $imageObject = Image::make($file);
        } catch (Exception $imageMakeException) {
            $imageObject = Image::make('public/assets/images/favicon.ico');
            Log::error($imageMakeException->getMessage());
        } finally {
            try {
                if ($imageObject instanceof \Intervention\Image\Image) {
                    if ($imageObject->resize(256, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->crop(256, 256, 0, 0)
                        ->save($tmpPath . $fileName, 80, $extension)) {
                        return $tmpPath . $fileName;
                    } else {
                        return null;
                    }
                }
            } catch (Exception $imageSaveException) {
                Log::error($imageSaveException->getMessage());

                return null;
            }
        }

        return null;
    }
}
