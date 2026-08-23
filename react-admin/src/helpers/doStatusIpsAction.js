/* 
 * doStatusIpsAction
 *
 * Task: do a required action in the status ip  tables over all ids provided in ids array . 
 *  
 * Params:
 *          restUrl - url, the api url to get the data
 *          nonce - string, the token to ensure the conection
 *          action - string, the required action
 *          ids - array, the ids over the action will be performed
 * Return:
 *          value success = true, fail = false
 */
export const doStatusIpsAction = async (restUrl, nonce, action, ids =[] ) => {

    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    // create the FormData to store the Data of query
    let apiData = new FormData();
//    apiData.append('action','do-login-attempts-status-ips');
    apiData.append('doAction',action);
    apiData.append('ids',JSON.stringify(ids))
    //apiData.append('ids',ids.toString());


    // send the query to the api endpoint
    const resp = await fetch(restUrl+'/do-login-attempts-status-ips',{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        const data = await resp.json();
        return data.success === true;
    } else return false
}