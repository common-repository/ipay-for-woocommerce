
/**
 * Initialize the custom setting field logic in window on load.
 */

window.onload = () =>{
    init_ipaytext_setting();
    init_custom_styles();
}


/**
 * Setting the event listeners for the custom setting fields.
 */

const init_ipaytext_setting = () => {

    let ipaytext_gen_btns = document.getElementsByClassName('ipaytext-gen-btn');
    let ipaytext_cp_btns = document.getElementsByClassName('ipaytext-cp-btn');
    let ipaytext_redirect_url_gen_btns = document.getElementsByClassName('ipaytext-gen-btn-ru');

    for(let ipaytext_gen_btn of ipaytext_gen_btns){

        let id = ipaytext_gen_btn.getAttribute('id');
        let split_id = id.substring(7);
        
        let ipaytext_input = document.getElementById(split_id);
        let random_number = generate_random_string(20);

        if(ipaytext_input.value.length === 0 || ipaytext_input.value === "")
            ipaytext_input.value = random_number;

        ipaytext_gen_btn.addEventListener('click', ()=>{
            random_number = generate_random_string(20);
            ipaytext_input.value = random_number;
        });

    }

    for(let ipaytext_cp_btn of ipaytext_cp_btns){
        
        let id = ipaytext_cp_btn.getAttribute('id');
        let split_id = id.substring(8);
        let tooltip_id = 'tool-tip' + split_id;

        let ipaytext_input = document.getElementById(split_id);
        let copy_to_clipboard_tooltip = document.getElementById(tooltip_id);
        
        ipaytext_cp_btn.addEventListener('click', ()=>{
            ipaytext_input.select();
            document.execCommand('copy');
            copy_to_clipboard_tooltip.innerHTML = "Copied !!!";
        });

        ipaytext_cp_btn.addEventListener('mouseover', ()=>{
            copy_to_clipboard_tooltip.classList.remove('ipay-custom-hidden');
        });

        ipaytext_cp_btn.addEventListener('mouseleave', ()=>{
            copy_to_clipboard_tooltip.innerHTML = "Copy to Clipboard";
            copy_to_clipboard_tooltip.classList.add('ipay-custom-hidden');
        });

    }

    for(let ipaytext_gen_btn of ipaytext_redirect_url_gen_btns){

        let id = ipaytext_gen_btn.getAttribute('id');
        let split_id = id.substring(10);

        let ipaytext_input = document.getElementById(split_id);

        let url = ipaytext_gen_btn.getAttribute('data-url')

        ipaytext_gen_btn.addEventListener('click', ()=>{
            ipaytext_input.value = url
        });
        
    }

}

const generate_random_string = (length) =>{
    let character_set = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for(let i=0; i<length; i++){
        result += character_set.charAt(Math.floor(Math.random() * character_set.length));
    }
    result += "#";
    return result;
}

const init_custom_styles = () =>{
    //Custom Styling Goes Here
}