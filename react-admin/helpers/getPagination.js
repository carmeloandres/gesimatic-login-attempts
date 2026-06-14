/* 
 * getPagination
 *
 * Task: gets from server the total items and total pages of a database table. 
 *  
 * Params:
 *          restUrl - the api url to get the data
 *          nonce - the token to ensure the conection
 *          table - The table to get the pagination information.
 * Return:
 *          object success = {'items': number, 'pages' : number} fail = {} empty object
 */
export const getPagination = async (restUrl, nonce, query ) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    // create the FormData to store the Data of query
    let apiData = new FormData();
    apiData.append('action','get_login_attempts_pagination');

    if ((query != undefined) && (query.filterStatus != undefined))
        apiData.append('filterStatus',query.filterStatus);
    else apiData.append('filterStatus','');
 

    // send the query to the api endpoint
    const resp = await fetch(restUrl,{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        return await resp.json();
    } else return {items: 0, pages: 0}
}