/*
    Component Accordion

    params: 
        showHide => boolean ( a boolean to show or hide the accordion)
        onChange => funtion (handler to change the showHide when is clicked)


    Task:   This component shows or Hide a content by clickin the button  

    To use the icons , this script must be loaded : <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
*/
import { useEffect, useState } from 'react'
import { icons } from '../icons'
import './GesimaticAccordion.css'

export const GesimaticAccordion = ({showHide, title= '', openLabel = '', closedLabel = '', onChange, children}) => {
    
    const [label, setLabel] = useState('')
    const [buttonClass, setButtonClass] = useState('')

    useEffect(() => {
        if (showHide){
            setLabel(openLabel)
            setButtonClass('gesimatic-accordion-button open')
        } else {
            setLabel(closedLabel)
            setButtonClass('gesimatic-accordion-button')
        }
    },[showHide])

   return(
            <>
                <div className='gesimatic-accordion'>
                    <div className={buttonClass} onClick={() => onChange( ! showHide)}><h2 className='gesimatic-accordion-button-label'>{title}</h2><div className='gesimatic-accordion-action-button' onClick={() => onChange( ! showHide)}><span style={{verticalAlign: 'middle'}}>{label}</span>{showHide &&  <icons.arrow_up_circle className={'bi'} name='upAccordion' onClick={() => onChange( ! showHide)}/>}{ ! showHide &&  <icons.arrow_down_circle className={'bi'} name='downAccordion' onClick={() => onChange( ! showHide)}/>} </div></div>
                        <div className='gesimatic-accordion-content' style={{display: (showHide)? 'block' : 'none'}}>
                            {children}
                        </div>                
                </div>            
            </>
    )
    
}