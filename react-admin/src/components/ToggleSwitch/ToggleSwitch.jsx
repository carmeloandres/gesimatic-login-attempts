/*
    Component ToggleSwitch

    params: 
        value => boolean ( a boolean to set on or off the switcher)
        onChange => funtion (handler to change the value when is clicked)


    Task:   This component show a toggler switcher to set on or off an option  
*/
import './ToggleSwitch.css'

export const ToggleSwitch = ({value, onChange}) => {
    
   return(
            <label class="switch">
                <input type="checkbox" onClick={() => {onChange( ! value )}} checked={value}/>
                <span class="slider round" ></span>
            </label>
    )
    
}