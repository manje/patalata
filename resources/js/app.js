import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.css';

import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

window.L = L;



import Editor from '@toast-ui/editor'
import '@toast-ui/editor/dist/toastui-editor.css';



const elementos = document.querySelectorAll('.editortoast');

elementos.forEach((elemento) => {
    console.log('creo un editor');
    const editor = new Editor({
        el: elemento,
        height: '400px',
        placeholder: 'Write something cool!',
        initialEditType: 'wysiwyg',
        autofocus: false,
        initialValue: elemento.innerHTML.trim()
    })
    editor.addHook('change', () => {
        const contenido = editor.getMarkdown();
        document.getElementById(elemento.attributes.dataid.value).value = contenido;
      });
      



});
