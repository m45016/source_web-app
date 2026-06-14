
'use strict';

import API from './apiModule.js';

document.getElementsByName('sendEmail_btn')[0].addEventListener('click', async (e) => {

  let email = document.getElementsByName('email')[0].value;
  let message = document.getElementsByClassName('message')[0];

  message.innerText = "";

  if (
    email.length !== 0 &&
    email.includes('@') &&
    email[email.length - 1] !== '@'
  ) {

    e.preventDefault();

    message.innerText = "Отправка сообщения на почту...";

    let csrf = document.getElementsByName('CSRF')[0].content;

    let json = {
      email,
      csrf
    };

    json = JSON.stringify(json);

    try {
      let response = await API.send('auth','sendEmail', json);

      if(response.success){
        document.getElementsByClassName('form')[0].classList.add('hidden');
        document.getElementsByClassName('token')[0].classList.remove('hidden');
      }

      message.innerText = "";

    } catch (e) {
      message.innerText = `Ошибка: ${e.message}`;
    }
  }
});