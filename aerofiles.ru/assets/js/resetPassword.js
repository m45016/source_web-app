
'use strict';

import API from './apiModule.js';

document.getElementsByName('resetPassword_btn')[0].addEventListener('click', async (e) => {

  let message = document.getElementsByClassName('message')[0];
  let password = document.getElementsByName('password')[0].value;
  let rep_password = document.getElementsByName('rep_password')[0].value;

  message.innerText = "";

  if(password !== rep_password){
    e.preventDefault();
    message.innerText = 'Пароли не совпадают';
  }
  else if (
    /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(password) &&
    /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(rep_password)
  ) {

    e.preventDefault();

    message.innerText = "Установка нового пароля...";

    let csrf = document.getElementsByName('CSRF')[0].content;

    let json = {
      password,
      rep_password,
      csrf
    };

    json = JSON.stringify(json);

    try {
      let response = await API.send('auth','resetPasswordUser', json);

      if(response){
        location = 'auth.php';
      }

      message.innerText = "";

    } catch (e) {
      message.innerText = `Ошибка: ${e.message}`;
    }
  }
});