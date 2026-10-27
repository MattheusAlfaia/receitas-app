<?php

namespace App\Services;

use App\Models\Receita;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ReceitaImageService
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function store(Receita $receita, UploadedFile $image): string
    {
        $extension = strtolower($image->getClientOriginalExtension());
        $filename = "imagem_{$receita->id}.{$extension}";
        $path = "receitas/{$filename}";

        if (Storage::disk('public')->putFileAs('receitas', $image, $filename) === false) {
            throw new RuntimeException('Não foi possível armazenar a imagem da receita.');
        }

        return $path;
    }

    public function delete(Receita $receita): void
    {
        foreach (self::EXTENSIONS as $extension) {
            Storage::disk('public')->delete($this->path($receita, $extension));
        }
    }

    public function deleteOtherExtensions(Receita $receita, string $storedPath): void
    {
        foreach (self::EXTENSIONS as $extension) {
            $path = $this->path($receita, $extension);

            if ($path !== $storedPath) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function existingPath(Receita $receita): ?string
    {
        foreach (self::EXTENSIONS as $extension) {
            $path = $this->path($receita, $extension);

            if (Storage::disk('public')->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function path(Receita $receita, string $extension): string
    {
        return "receitas/imagem_{$receita->id}.{$extension}";
    }
}
