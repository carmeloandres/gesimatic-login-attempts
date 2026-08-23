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
//    apiData.append('action','get-login-attempts-status-ips');
    apiData.append('query',JSON.stringify({...query}));

    // send the query to the api endpoint
    const resp = await fetch(restUrl+'/get-login-attempts-status-ips',{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        const data = await resp.json();
        return Array.isArray(data.statusIps) ? data.statusIps : [];
    } else return []
}