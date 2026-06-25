/* 
 * getLoaginAttemptsSettings
 *
 * Task: gets from server the login attempts settings. 
 *  
 * Params:
 *          restUrl - the api url to get the data
 *          nonce - the token to ensure the conection
 * Return:
 *          object success = the requested data, fail = empty object
 */
export const getLoginAttemptsSettings = async (restUrl, nonce ) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    // create the FormData to store the Data of query
    let apiData = new FormData();
    apiData.append('action','get_login_attempts_settings');

    // send the query to the api endpoint
    const resp = await fetch(restUrl,{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    // recive the resquest from api and obtain the json data
    if (resp.ok){
        return await resp.json();
    } else return { attempts : 4, // max times of fail login before procede with a lock
                    initialLock : 20, // initial period of time in minutes of a short lock
                    multiplier: 2, //  Multiplier to increment the Periods of lock
                    logedInAlert : true, //Sets the alert (sendig an email) at loged in user
                    triggerRoles:['administrator'] //Roles that trigger the alert at loged in user
                }
}