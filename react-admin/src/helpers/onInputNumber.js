/* 
 * onInputNumber
 *
 * Task: Checks the value of number an update the object with the name and value. 
 *  
 * Params:
 *          event - the event send in the acction
 *          object - the object to update
 * Return:
 *          object, success = the update object whith new data, fail = empty object
 */
export const onInputNumber = (event, object) => {
    let value = parseInt(event.target.value)
    let max = parseInt(event.target.max)
    let min = parseInt(event.target.min)
    let name = event.target.name
    if (value > max)
        value = max;
    if ((value < min))
        value = min;

    return {...object, [name] : value}

}