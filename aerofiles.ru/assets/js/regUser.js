
'use strict';

import API from "./apiModule.js";

document.getElementsByName('reg_btn')[0].addEventListener('click', async (e) => {

  let message = document.getElementsByClassName('message')[0];
  let pass = document.getElementsByName('password')[0].value;
  let rep_pass = document.getElementsByName('rep_password')[0].value;
  let login = document.getElementsByName('login')[0].value;
  let email = document.getElementsByName('email')[0].value;

  message.innerText = "";

  if (pass !== rep_pass) {

    e.preventDefault();
    message.innerHTML = "Пароли должны<br>совпадать";
    return STATUS_REQUEST['dataError'];

  }
  if (login.length > 2 &&
    rep_pass.length != 0 &&
    email.length != 0 &&
    /(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}/.test(pass) &&
    email.includes('@') &&
    email[email.length - 1] !== '@'
  ) {

    e.preventDefault();

    let csrf = document.getElementsByName('CSRF')[0].content;

    let json = {
      login,
      pass,
      rep_pass,
      email,
      csrf,
      reg: true
    };

    json = JSON.stringify(json);

    message.innerText = "Получение данных от сервера";

    try {
      console.log(`Регистрация пользователя ${login}`);
      let response = await API.send('auth','regUser', json);

      message.innerText = "Регистрация успешна";
      location = 'auth.php';

    } catch (e) {
      console.error(e);
      message.innerText = `Ошибка: ${e.message}`;
    }
  }
});