<?php

namespace App\Service\App;

use App\Common\Config\AppConfig;
use App\Common\Config\FileSystemConfig;
use App\Common\LogoGenerator;
use App\Common\UploadFile;
use App\Entity\AppLogo;
use App\Model\Logo;
use App\Model\Request\AppRequest;
use App\Model\RGB;
use App\Repository\App\AppLogoRepositoryInterface;
use App\Service\BaseService;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class AppLogoService extends BaseService implements AppLogoServiceInterface
{
    private UploadFile $uploadFile;
    private LogoGenerator $logoGenerator;
    private AppLogoRepositoryInterface $appLogoRepository;

    public function __construct(
        UploadFile $uploadFile,
        LogoGenerator $logoGenerator,
        AppLogoRepositoryInterface $appLogoRepository
    ) {
        $this->uploadFile = $uploadFile;
        $this->logoGenerator = $logoGenerator;
        $this->appLogoRepository = $appLogoRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createAppLogo(AppLogo $appLogo, AppRequest $request): AppLogo
    {
        if ($request->isUpdateLogo()) {
            return self::updateAppLogo($appLogo, $request);
        }

        if (null === $appLogo->getFilePath()) {
            return self::generateRandomLogo($appLogo, $request->getName());
        }

        return $appLogo;
    }

    /**
     * Force update the logo of app
     *
     * @param AppLogo $appLogo
     * @param AppRequest $request
     *
     * @return AppLogo
     *
     * @throws Exception
     */
    private function updateAppLogo(AppLogo $appLogo, AppRequest $request): AppLogo
    {
        self::deleteOldLogo($appLogo->getFilePath());

        if (null !== $request->getLogo()) {
            $filePath = $this->uploadFile->uploadFile(
                $request->getLogo(),
                self::getLogoDirectory()
            );
            $appLogo
                ->setLogoOption(AppConfig::CUSTOM)
                ->setFilePath($filePath)
                ->setPublicPath(self::getPublicURlLogo($filePath));

            return $appLogo;
        }

        return self::generateRandomLogo($appLogo, $request->getName());
    }

    /**
     * Deletes the old logo file from the provided path.
     *
     * @param string|null $filePath
     *
     * @return void
     */
    private function deleteOldLogo(?string $filePath): void
    {
        if ($filePath) {
            $fileSystem = new Filesystem();
            try {
                $fileSystem->remove($filePath);
            } catch (Exception $exception) {
            }
        }
    }

    /**
     * Retrieves the directory path for the logo files.
     *
     * @return string
     *
     * @throws Exception
     */
    private function getLogoDirectory(): string
    {
        $path = $this->getParam('kernel.project_dir').FileSystemConfig::UPLOAD_PATH.'/logo';

        $this->uploadFile->createDirectory($path);

        return $path;
    }

    /**
     * Returns the public URL for the given file path.
     *
     * @param string $filePath
     *
     * @return string
     */
    private function getPublicURlLogo(string $filePath): string
    {
        $file = new File($filePath);
        $baseUrl = $this->getParam('app_url');

        return $baseUrl.FileSystemConfig::PUBLIC_PATH.'/logo/'.$file->getFilename();
    }

    /**
     * Generates a random logo based on the provided AppLogo and name.
     *
     * @param AppLogo $appLogo
     * @param string $name
     *
     * @return AppLogo
     *
     * @throws Exception
     */
    private function generateRandomLogo(AppLogo $appLogo, string $name): AppLogo
    {
        $initials = $this->logoGenerator->getInitials($name);
        $rgb = self::generateBackgroundColor($initials);

        $logo = (new Logo())
            ->setInitials($initials)
            ->setRgb($rgb)
            ->setDestination(self::getLogoDirectory())
            ->setFontPath(self::getFontPathLogo());
        $filePath = $this->logoGenerator->generateLogo($logo);

        return $appLogo->setInitials($initials)
            ->setLogoOption(AppConfig::DEFAULT)
            ->setBgColor($rgb->getString())
            ->setFilePath($filePath)
            ->setPublicPath(self::getPublicURlLogo($filePath));
    }

    /**
     * Generates a background color based on the provided initials.
     *
     * @param string $initials
     *
     * @return RGB
     */
    private function generateBackgroundColor(string $initials): RGB
    {
        $bgColor = $this->logoGenerator->generateRGB();

        if (
            $this->isCombinationBgColorAndInitialsUnique(
                $bgColor->getString(),
                $initials
            )
        ) {
            self::generateBackgroundColor($initials);
        }

        return $bgColor;
    }

    /**
     * Checks if the combination of background color and initials is unique.
     *
     * @param string $bgColor
     * @param string $initials
     *
     * @return bool
     */
    private function isCombinationBgColorAndInitialsUnique(string $bgColor, string $initials): bool
    {
        return $this->appLogoRepository
            ->isCombinationUnique($bgColor, $initials);
    }

    /**
     * Returns the font path for the logo.
     *
     * @return string
     */
    private function getFontPathLogo(): string
    {
        return $this->getParam('kernel.project_dir').'/public/font/roboto/Roboto-Regular.ttf';
    }
}