<?php

namespace Bestkit\Api\Controller;

use Bestkit\Http\RequestUtil;
use Bestkit\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Intervention\Image\Image;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Tobscure\JsonApi\Document;

abstract class UploadImageController extends ShowSiteController
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var Filesystem
     */
    protected $uploadDir;

    /**
     * @var string
     */
    protected $fileExtension = 'png';

    /**
     * @var string
     */
    protected $filePathSettingKey = '';

    /**
     * @var string
     */
    protected $filenamePrefix = '';

    /**
     * @param SettingsRepositoryInterface $settings
     * @param Factory $filesystemFactory
     */
    public function __construct(SettingsRepositoryInterface $settings, Factory $filesystemFactory)
    {
        $this->settings = $settings;
        $this->uploadDir = $filesystemFactory->disk('bestkit-assets');
    }

    /**
     * {@inheritdoc}
     */
    public function data(ServerRequestInterface $request, Document $document)
    {
        RequestUtil::getActor($request)->assertAdmin();

        $file = Arr::get($request->getUploadedFiles(), $this->filenamePrefix);

        $encodedImage = $this->makeImage($file);

        if (($path = $this->settings->get($this->filePathSettingKey)) && $this->uploadDir->exists($path)) {
            $this->uploadDir->delete($path);
        }

        $uploadName = $this->filenamePrefix.'-'.Str::lower(Str::random(8)).'.'.$this->fileExtension;

        $this->uploadDir->put($uploadName, $encodedImage);

        $this->settings->set($this->filePathSettingKey, $uploadName);

        return parent::data($request, $document);
    }

    /**
     * @param UploadedFileInterface $file
     * @return Image
     */
    abstract protected function makeImage(UploadedFileInterface $file): Image;
}
