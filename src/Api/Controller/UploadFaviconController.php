<?php

namespace Bestkit\Api\Controller;

use Bestkit\Foundation\ValidationException;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadFaviconController extends UploadImageController
{
    protected $filePathSettingKey = 'favicon_path';

    protected $filenamePrefix = 'favicon';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @param SettingsRepositoryInterface $settings
     * @param Factory $filesystemFactory
     */
    public function __construct(SettingsRepositoryInterface $settings, Factory $filesystemFactory, TranslatorInterface $translator, ImageManager $imageManager)
    {
        parent::__construct($settings, $filesystemFactory);

        $this->translator = $translator;
        $this->imageManager = $imageManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function makeImage(UploadedFileInterface $file): Image
    {
        $this->fileExtension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);

        if ($this->fileExtension === 'ico') {
            // @todo remove in 2.0
            throw new ValidationException([
                'message' => strtr($this->translator->trans('validation.mimes'), [
                    ':attribute' => 'favicon',
                    ':values' => 'jpeg,png,gif,webp',
                ])
            ]);
        }

        $encodedImage = $this->imageManager->make($file->getStream()->getMetadata('uri'))->resize(64, 64, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        })->encode('png');

        $this->fileExtension = 'png';

        return $encodedImage;
    }
}
