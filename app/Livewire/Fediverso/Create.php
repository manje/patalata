<?php

namespace App\Livewire\Fediverso;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

use App\ActivityPub\ActivityPub;

use App\Models\Nota;
use App\Models\Apfile;

class Create extends Component
{
    use WithFileUploads;

    public $text = '';
    public $sensitive = false;
    public $summary = '';
    public $media = [];
    public $photos = [];
    public $altText=[];
    public $maxLength = 250;
    public $isFormSubmitted = false;

    protected $rules = [
        'text' => 'required|max:250',
        'media.*' => 'mimes:jpeg,jpg,png,mp4,mp3|max:10240', // 10MB max
    ];

    
    public function submit()
    {
        Log::info("submit");
        $this->validate([
            'text' => 'required|string|max:250',
            'summary' => 'nullable|string|max:250|required_if:isSensitive,true',
            'sensitive' => 'boolean',
            'media.*' => 'mimes:webp,jpeg,png,jpg,gif,mp4,mp3,wav,ogg,ogv|max:10240',
        ]);
        Log::info("validado");
        Log::info(print_r($this->altText,1));
        $this->isFormSubmitted = true;
        $user=auth()->user();
        $user_id=$user->id;
        $team_id=$user->current_team_id;
        $nota = Nota::create([
            'user_id' => $user_id,
            'team_id' => $team_id,
            'content' => $this->text,
            'sensitive' => $this->sensitive,
            'summary' => $this->summary
        ]);
        if ($nota)
        {
            Log::info("dispatch");
            $this->dispatch('formSubmitted');
            $dir = 'apfiles/'.now()->format('Y/m');
            foreach ($this->media as $k=>$file) {
                Log::info(print_r("aaa", true));
                $stored=$file->store($dir,"public");
                Apfile::create([
                    'file_path' => $stored,
                    'file_type' => $file->GetMimeType(),
                    'alt_text' => $this->altText[$k] ?? '', // Asegura que haya un altText asociado
                    'apfileable_id' => $nota->id,
                    'apfileable_type' => Nota::class
                ]);

            }
            session()->flash('message', 'Publicación exitosa');
            $this->text = '';
            $this->sensitive = false;
            $this->summary = '';
            $this->media = [];
            $this->altText=[];
            $this->photos=[];
        }
        else
            session()->flash('message', 'Publicación fallida');


    }

    public function render()
    {
        return view('livewire.fediverso.create');
    }
}
