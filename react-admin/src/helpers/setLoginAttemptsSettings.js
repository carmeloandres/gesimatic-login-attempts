/* 
 * setLoginAttemptsSettings
 *
 * Task: sets the login attempts settings in database. 
 *  
 * Params:
 *          restUrl - the api url to get the data
 *          nonce - the token to ensure the conection
 *          settings - object with the data
 * Return:
 *          boolean success = true, fail = false
 */
export const setLoginAttemptsSettings = async (restUrl, nonce, settings ) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    // create the FormData to store the Data of query
    let apiData = new FormData();
//    apiData.append('action','set-login-attempts-settings');
    apiData.append('settings',JSON.stringify({...settings}));


    // send the query to the api endpoint
    const resp = await fetch(restUrl+'/set-login-attempts-settings',{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        let data = await resp.json();
        return data;

    } else return false
}