<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Uploader
{
	public function __construct(private Filesystem $fs, private $profilFolder, private $profilFolderPublic)
	{
		
	}

	public function uploadProfileImage(UploadedFile $picture, string $oldPicturePath = null): string
	{
		$folder = $this->profilFolder;
		$ext = $picture->guessExtension() ?? 'bin';
		$filename = bin2hex(random_bytes(10)) . '.' . $ext;
		$picture->move($folder, $filename);
		if($oldPicturePath){
			$this->fs->remove($folder . '/' . pathinfo($oldPicturePath, PATHINFO_BASENAME));
		}
		return $this->profilFolderPublic . '/' . $filename;
	}






	
}