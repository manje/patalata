<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Session;

use Livewire\Component;
use Livewire\WithFileUploads;

class FileUpload extends Component
{
    use WithFileUploads;

    public $image;
    public $name;
    public $tempPath;

    public function mount($name,$old=false)
    {
        \Illuminate\Support\Facades\Log::info(" montando $name con ".Session::get('temp_image_'.$name));
        $this->name = $name;
        $this->tempPath = Session::get('temp_image_'.$name);
        if (!$this->tempPath)
            $this->tempPath=$old;
    }

    public function updatedImage()
    {
        $this->validate([
            'image' => 'image|max:10240', // 10MB máximo
        ]);
        $this->tempPath = $this->image->store('tmp_uploads','public');
        \Illuminate\Support\Facades\Log::info('image updated'.print_r($this->tempPath,true));
        Session::put('temp_image_'.$this->name, $this->tempPath);
    }

    public function render()
    {
        return view('livewire.file-upload');
    }
}
