<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class Multimedia extends Component
{
    use WithFileUploads;

    public $media = [];
    public $files = [];
    public $uniqid=null;

    public function mount($files=false,$uniqid=false)
    {
        if ($files)
        {
            $this->files=[];
            foreach ($files as $file)
            {
                $this->files[]=['path'=>$file['file_path'],'mime'=>$file['file_type'],'alt'=>$file['alt_text']];
            }
        }
        if ($uniqid) {
            $this->uniqid = $uniqid;
            $this->files = session()->get('multimedia_tmp_' . $uniqid, []);
        } else {
            $this->uniqid = uniqid();
        }
        Session::put('multimedia_tmp_'.$this->uniqid, $this->files);
    }

    public function removeFile($key)
    {
        // borro el fichero del storage
        Storage::disk('public')->delete($this->files[$key]['path']);
        unset($this->files[$key]);
        Session::put('multimedia_tmp_'.$this->uniqid, $this->files);

    }

    public function updateAlt()
    {
        Session::put('multimedia_tmp_'.$this->uniqid, $this->files);
    }

    public function updatedMedia()
    {
        foreach ($this->media as $file) {
            
            $mime=mime_content_type($file->getRealPath());
            $path=$file->store("temp_uploads", "public");
            $this->files[]=['path'=>$path,'mime'=>$mime,'alt'=>''];
        }
        Session::put('multimedia_tmp_'.$this->uniqid, $this->files);
    }

    public function render()
    {
        return view('livewire.multimedia',['uniqid'=>$this->uniqid]);
    }
}
