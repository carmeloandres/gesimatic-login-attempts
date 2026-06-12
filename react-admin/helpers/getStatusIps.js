/* 
 * getStatusIps
 *
 * Task: gets from server the status Ips information. 
 *  
 * Params:
 *          restUrl - the api url to get the data
 *          nonce - the token to ensure the conection
 *          query - an object with the query options,  structure -> {page : 1, orderAttempts : '', orderLockPeriod: '', orderLastAttempt: '', filterStatus : ''}
 * Return:
 *          object success = the requested data, fail = empty object
 */
export const getStatusIps = async (restUrl, nonce, query = {page : 1, orderAttempts : '', orderLockPeriod: '', orderLastAttempt: '', filterStatus : ''}) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    const { page, orderAttempts, orderLockPeriod, orderLastAttempt, filterStatus } = query

    // create the FormData to store the Data of query
    let apiData = new FormData();
    apiData.append('action','get_login_attempts_status_ips');
    apiData.append('page',page);
    apiData.append('orderAttempts',orderAttempts);
    apiData.append('orderLockPeriod',orderLockPeriod);
    apiData.append('orderLastAttempt',orderLastAttempt);
    apiData.append('filterStatus',filterStatus);

    // send the query to the api endpoint
    const resp = await fetch(restUrl,{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        return await resp.json();
    } else return {}
}