export class Datetime{
  date = new Date();
  formatter = new Intl.DateTimeFormat('ru',{year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'});
  getDate(){
    if(this.date.toString() === 'Invalid Date'){
      return null;
    }
    let date = this.formatter.format(this.date);
    date = date.replaceAll('.','-').replaceAll(',','');
    return date;
  }
  setDate(date){
    date = new Date(date+'Z');
    this.date = date;
  }
}