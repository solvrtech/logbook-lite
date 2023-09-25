<?php

namespace App\Common;

use App\Model\Logo;
use App\Model\RGB;
use Exception;
use GdImage;
use Ketut\RandomString\Random;

class LogoGenerator
{
    /**
     * Generates a logo image based on the provided Logo object.
     *
     * @param Logo $logo
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateLogo(Logo $logo): string
    {
        $logoImage = imagecreatetruecolor(100, 100);

        $backgroundColor = self::generateTrueColor($logoImage, $logo->getRgb());
        imagefill($logoImage, 0, 0, $backgroundColor);
        self::drawInitialsOnLogo($logoImage, $logo);

        $logoPath = $logo->getDestination().'/'.self::generateFileName().'.png';
        imagepng($logoImage, $logoPath);
        imagedestroy($logoImage);

        return $logoPath;
    }

    /**
     * Generates a true color value for the given image resource and RGB color.
     *
     * @param GdImage $image
     * @param RGB $RGB
     *
     * @return int
     */
    private function generateTrueColor(GdImage $image, RGB $RGB): int
    {
        return imagecolorallocate(
            $image,
            $RGB->getRed(),
            $RGB->getGreen(),
            $RGB->getBlue()
        );
    }

    /**
     * Draws the initials of the logo on the provided logo image.
     *
     * @param GdImage $logoImage
     * @param Logo $logo
     *
     * @return void
     */
    private function drawInitialsOnLogo(GdImage $logoImage, Logo $logo): void
    {
        $textColor = imagecolorallocate($logoImage, 255, 255, 255);
        $initials = strtoupper($logo->getInitials());
        $bbox = imagettfbbox(
            40,
            0,
            $logo->getFontPath(),
            $initials
        );
        $textWidth = $bbox[2] - $bbox[0];
        $textHeight = $bbox[3] - $bbox[5];
        $x = (100 - $textWidth) / 2;
        $y = (100 - $textHeight) / 2 + $textHeight;

        imagettftext(
            $logoImage,
            40,
            0,
            $x,
            $y,
            $textColor,
            $logo->getFontPath(),
            $initials
        );
    }

    /**
     * Get initials from a given name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getInitials(string $name): string
    {
        $words = explode(" ", $name);
        $letter = substr($words[0], 0, 1);

        if (1 < count($words)) {
            $letter .= substr($words[1], 0, 1);
        }

        return strtolower($letter);
    }

    /**
     * Generates a random filename.
     *
     * @throws Exception
     */
    private function generateFileName(): string
    {
        return (new Random())
            ->length(64)
            ->lowercase()
            ->numeric()
            ->generate();
    }

    /**
     * Generates a random RGB color.
     *
     * @return RGB
     */
    public function generateRGB(): RGB
    {
        return (new RGB())
            ->setRed(mt_rand(50, 200))
            ->setGreen(mt_rand(50, 200))
            ->setBlue(mt_rand(50, 200));
    }
}