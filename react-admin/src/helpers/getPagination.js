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
 *          Pagination data; throws when the request fails or the response is invalid.
 */
export const getPagination = async (restUrl, nonce, query ) => {
    // create the header with the nonce token
    const headers = new Headers({
        'X-WP-Nonce': nonce 
    })    

    // create the FormData to store the Data of query
    let apiData = new FormData();
//    apiData.append('action','get-login-attempts-pagination');

    if ((query != undefined) && (query.filterStatus != undefined))
        apiData.append('filterStatus',query.filterStatus);
    else apiData.append('filterStatus','');
 

    // send the query to the api endpoint
    const resp = await fetch(restUrl+'/get-login-attempts-pagination',{
        method: 'POST',
        headers: headers,
        body:apiData
    })

    if (!resp.ok) {
        throw new Error('Unable to retrieve pagination');
    }

    const data = await resp.json();
    const isCount = (value) => (
        (typeof value === 'number' || (typeof value === 'string' && /^\d+$/.test(value)))
        && Number.isSafeInteger(Number(value))
        && Number(value) >= 0
    );
    if (data?.success !== true || !isCount(data.items) || !isCount(data.pages)) {
        throw new Error('Invalid pagination response');
    }

    return {...data, items: Number(data.items), pages: Number(data.pages)};
}
