'use strict';

import API from "./apiModule.js";
import { modal } from "./modalWindow.js";
import { Datetime } from "./datetimeModule.js";

function formattedDates() {
  let datetime = new Datetime();

  let dates = Object.values(document.getElementsByClassName('date'));
  dates.forEach(date => {
    datetime.setDate(date.innerText);
    let formattedDate = datetime.getDate();
    if(formattedDate === null){
      return 0;
    }
    date.innerText = formattedDate;
  });
}


document.addEventListener('click', async (e) => {
  if (e.target.classList.contains('setChange')) {
    let isAdmin = e.target.parentNode.parentNode.children[0].children[0].children[0].checked;
    let tariff = e.target.parentNode.parentNode.children[1].children[0].value;
    let idUser = Number(e.target.parentNode.parentNode.parentNode.children[0].children[0].innerText);
    let csrf = document.getElementsByName('CSRF')[0].content;

    let json = {
      isAdmin,
      tariff,
      idUser,
      csrf
    };

    json = JSON.stringify(json);

    try {
      console.log(`Изменение данных пользователя: Права: ${isAdmin}; Тариф: ${tariff}; Пользователь: ${idUser}.`);
      let response = await API.send('admin', 'updateUserData', json);

      if (response !== true) {
        throw new Error('Данные не корректны');
      }

      await modal.alert('Данные пользователя изменены');
      console.log('Данные изменены');
      document.getElementById('findUser').click();
    } catch (e) {
      console.error(e);
      await modal.alert(e.message);
    }
  }
});

document.getElementById('findUser').addEventListener('click', async (e) => {
  e.preventDefault();

  let login = document.querySelector("input[name='login']").value;
  let container = document.getElementsByClassName('container')[0];
  let csrf = document.getElementsByName('CSRF')[0].content;
  
  let json = {
    login,
    csrf
  };

  json = JSON.stringify(json);

  let user = null;

  try {
    console.log(`Поиск пользователя ${login}`);

    let response = await API.send('admin', 'getUser', json);

    let tariffs = '';

    response.tariffs.forEach(tariff => {
      tariffs += `<option value="${tariff.name}">${tariff.name.toUpperCase()}</option>`;
    });

    user = `<div class="user">
          <div class="rowData">ID: <span class='idUser'>${response.id_user}</span></div>
          <div class="rowData">Имя пользователя: <span class='nameUser'>${response.login}</span></div>
          <div class="rowData">Активен: <span>${response.isActive}</span></div>
          <div class="rowData">Email: <span class='EmailUser'>${response.email}</span></div>
          <div class="rowData">Права администратора: <span class='isAdmin'>${response.isAdminText}</span></div>
          <div class="rowData">Баланс: <span>${response.balance} руб.</span></div>
          <div class="rowData">Тариф: <span>${response.tariff_name.toUpperCase()}</span></div>
          <div class="rowData">Дата оплаты: <span class='date'>${response.date_payment !== null ? response.date_payment : 'Не оплачен'}</span></div>
          <div class="rowData">Действителен до: <span class='date'>${response.tariffValidTo !== null ? response.tariffValidTo : 'Не оплачен'}</span></div>
          <div class="rowData">Тариф действителен: <span>${response.isPaymentTariff}</span></div>
          <div class="rowData">Сводбодное место в хранилище: <span class='freeStorage'>${response.freeSize}</span></div>
          <div class="rowData">Занятое место в хранилище: <span class='sizeStorage'>${response.sizeStorage}</span></div>
          <div class="rowData">Максимальный размер хранилища: <span class='maxStorage'>${response.maxSizeStorage}</span></div>
          <div class="actionUser">
            <div>Сделать админом: <span><input type="checkbox" class="setAdmin" ${response.isAdminCheckBox}></span></div>
            <div>Установить тариф: <select class='setMaxStorage'>
                <option value="No Change">No Change</option>
                ${tariffs}
              </select>
            </div>
            <div><button class='setChange'>Изменить данные</button></div>
          </div>
        </div>`;
    console.log('Пользователь найден');
  } catch (e) {
    console.error(e);
    user = `<div class="text-center">Ошибка: ${e.message}</div>`;
  }

  container.innerHTML = '';
  container.insertAdjacentHTML('beforeend', user);

  formattedDates();

});

document.addEventListener("DOMContentLoaded", formattedDates)