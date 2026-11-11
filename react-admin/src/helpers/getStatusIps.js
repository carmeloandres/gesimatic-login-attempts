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
 *          Array of records; throws when the request fails or the response is invalid.
 */
export const getStatusIps = async (restUrl, nonce, query = {page : 1, orderAttempts : '', orderLockPeriod: '', orderLastAttempt: '', filterStatus : ''}) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

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

    if (!resp.ok) {
        throw new Error('Unable to retrieve IP status');
    }

    const data = await resp.json();
    if (data?.success !== true || !Array.isArray(data.statusIps)) {
        throw new Error('Invalid IP status response');
    }

    return data.statusIps;
}
