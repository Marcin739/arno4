<?php

namespace App\Http\Livewire;

use App\Models\Page;
use Illuminate\Support\Str;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;


class Pages extends Component
{
    use WithPagination;
    public $modalFormVisible = false;
    public $modalConfirmDeleteVisible = false;
    public $modelId;
    public $title;
    public $slug;
    public $content;
    public $isSetToDefaultHomePage;
    public $isSetToDefaultNotFoundPage;

    /**
     * The validation rules
     * 
     * @return void
     */
    public function rules(){
        return [
            'title' => 'required',
            'slug' => ['required', Rule::unique('pages', 'slug')->ignore($this->modelId)],
            'content' => 'required'
            ];
    }

    public function mount(){
        //Resets the pagination after reloading the page
        $this->resetPage();
    }
    public function read(){
        return Page::paginate(5);
    }

    public function update(){
        $this->validate();
        $this->unassignDefaultHomePage();
        $this->unassignDefaultNotFoundPage();
        Page::find($this->modelId)->update($this->modelData());
        $this->modalFormVisible = false;
    }

    public function delete(){
        Page::destroy($this->modelId);
        $this->modalConfirmDeleteVisible = false;
        $this->resetPage();
    }

    public function updatedTitle($value){
        $this->slug = Str::slug($value);
    }

    public function create(){
        $this->validate();
        $this->unassignDefaultHomePage();
        $this->unassignDefaultNotFoundPage();
        Page::create($this->modelData());
        $this->modalFormVisible = false;
        //$this->resetVars();
        $this->reset();
    }

    public function updatedIsSetToDefaultHomePage(){
        $this->isSetToDefaultNotFoundPage = null;
    }

    public function updatedIsSetToDefaultNotFoundPage(){
        $this->isSetToDefaultHomePage = null;
    }
    /**
     * Shows the form modal
     * of the create function
     * 
     * @return void
     */
    public function createShowModal(){
        $this->resetValidation();
        $this->reset();
        $this->modalFormVisible = true;
    }

    public function updateShowModal($id){
        $this->resetValidation();
        $this->reset();
        $this->modelId = $id;
        $this->modalFormVisible = true;
        $this->loadModel();
    }


    public function deleteShowModal($id){
        $this->modelId = $id;
        $this->modalConfirmDeleteVisible = true;;
    }

    public function loadModel(){
        $data = Page::find($this->modelId);
        $this->title = $data->title;
        $this->slug = $data->slug;
        $this->content = $data->content;
        $this->isSetToDefaultHomePage = !$data->is_default_home ? null:true;
        $this->isSetToDefaultNotFoundPage = !$data->is_default_not_found ? null:true;
    }

    /**
     * The data for the model mapped
     * in this component
     * 
     * @return void
     */
    public function modelData(){
        return [
        'title' => $this->title,
        'slug' => $this->slug,
        'content' => $this->content,
        'is_default_home' => $this->isSetToDefaultHomePage,
        'is_default_not_found' => $this->isSetToDefaultNotFoundPage,
        ];
    }
/*
    public function resetVars(){
        $this->modelId = null;
        $this->title = null;
        $this->slug = null;
        $this->content = null;
        $this->isSetToDefaultHomePage = null;
        $this->isSetToDefaultNotFoundPage = null;
    }
*/
    private function unassignDefaultHomePage(){
        if($this->isSetToDefaultHomePage != null){
            Page::where('is_default_home', true)->update([
                'is_default_home' => false,
            ]);
        }
    }

    private function unassignDefaultNotFoundPage(){
        if($this->isSetToDefaultNotFoundPage != null){
            Page::where('is_default_not_found', true)->update([
                'is_default_not_found' => false,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pages', [
            'data' => $this->read()
        ]);
    }
}
