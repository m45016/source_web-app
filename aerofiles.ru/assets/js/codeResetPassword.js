import API from './apiModule.js';

let inputs = document.getElementsByClassName('partToken');
let btn = document.querySelector('.code');
let message = document.querySelector('.message');
inputs[0].focus();

btn.addEventListener('click', async (e) => {
  e.preventDefault();
  let token = "";
  message.innerText = "";
  Object.values(inputs).forEach(input => {
    token += input.value;
  });
  if (token.length !== 4) {
    message.innerText = 'Ошибка: код не полный';
    return 0;
  }

  message.innerText = "Проверка кода...";

  let csrf = document.getElementsByName('CSRF')[0].content;

  let json = {
    code: token,
    csrf
  }

  json = JSON.stringify(json);

  try{

    let response = await API.send('auth','verifyCode',json);

    if(response){
      message.innerText = "";
      document.getElementsByClassName('token')[0].classList.add('hidden');
      document.getElementsByClassName('formPasswords')[0].classList.remove('hidden');
    }

  }catch(e){
    message.innerText = `Ошибка: ${e.message}`;
  }

});

Object.values(inputs).forEach(input => input.addEventListener('input', (e) => {
  let arrInputs = Object.values(inputs);
  let idInput = arrInputs.indexOf(e.target);
  let input = e.target;
  input.value = input.value.replace(/\D/, '');
  if (input.value.length !== 0) {
    input.value = input.value[0];
    if (idInput !== arrInputs.length - 1) {
      idInput++;
      arrInputs[idInput].focus();
    }
  }
  else if(input.value.length === 0){
    if(idInput !== 0){
      idInput--;
      arrInputs[idInput].focus();
    }
  }
}));