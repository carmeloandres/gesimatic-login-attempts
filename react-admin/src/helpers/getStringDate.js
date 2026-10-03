/* 
 * getStringDate
 *
 * Task: convert a unix date, seconds from 1/1/1971 to date string. 
 *  
 * Params:
 *          seconds - the seconds transcurred from 1/1/1971 to date to convert to string
 * Return:
 *          string forma : YYYY/m/d hh:mm
 */
export const getStringDate = ( seconds ) => {

        let date = new Date();
        date.setSeconds( seconds )
        let year = date.getFullYear();
        let month = (date.getMonth() + 1).toString();
        if (month.length == 1)
                month = '0'+month;
        let day = date.getDate().toString();
        if (day.length == 1)
                day = '0'+day;
        let hours = date.getHours().toString();
        if (hours.length == 1)
                hours = '0'+hours;
        let minutes = date.getMinutes().toString();
        if (minutes.length == 1)
                minutes = '0'+minutes;
        let dateString = year+'/'+month+'/'+day+' '+hours+':'+minutes;    

        return dateString;
}