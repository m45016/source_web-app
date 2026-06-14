class ModalWindow {

  modal = null;
  Ok = null;
  Cancel = null;
  modalText = null;

  openModal(text) {

    if(this.modal !== null){
      this.modalText.innerText += `\n${text}`;
      return 1;
    }

    let modalWindow = `<div class="modalContainer">
                            <div class="modalWindow">
                              <div class="modalHeader">Подтвердите действие</div>
                              <div class="modalText"></div>
                              <div class="modalActions">
                                <button class="modalOk btn">ОК</button>
                                <button class="modalCancel btn">Отмена</button>
                              </div>
                            </div>
                          </div>`;

    document.body.insertAdjacentHTML("afterbegin", modalWindow);

    this.modal = document.querySelector('.modalContainer');
    this.Ok = document.querySelector('.modalOk');
    this.Cancel = document.querySelector('.modalCancel');
    this.modalText = document.querySelector('.modalText');
    this.modalText.innerText = text;
    setTimeout(()=>{
      this.modal.children[0].style.transform = "rotateX(0deg)";
      this.Ok.focus();
    },100);
  }

  alert(text) {

    return new Promise((resolve) => {

      this.openModal(text);

      this.Cancel.classList.add('hidden');

      let closeModal = () => {
        this.Ok.removeEventListener('click', closeModal);
        this.modal.children[0].style.transform = "rotateX(90deg)";
        setTimeout(() => {
          this.modal.remove();
          this.modal = null;
          resolve();
        }, 300);
      }
      this.Ok.addEventListener('click', closeModal);
    })

  }

  confirm(text) {

    return new Promise((resolve) => {

      if(this.modal!==null){
        return 1;
      }

      this.openModal(text);

      let closeModal = (bool) => {
        this.Ok.removeEventListener('click', confirmOk);
        this.Cancel.removeEventListener('click', confirmCancel);
        this.modal.children[0].style.transform = "rotateX(90deg)";
        setTimeout(() => {
          this.modal.remove();
          this.modal = null;
          resolve(bool);
        }, 300);
      }

      let confirmOk = () => {
        closeModal(true);
      }

      let confirmCancel = () => {
        closeModal(false);
      }

      this.Ok.addEventListener('click', confirmOk);
      this.Cancel.addEventListener('click', confirmCancel);

    })

  }
}

export const modal = new ModalWindow();