/*
    Component Accordion

    params: 
        showHide => boolean ( a boolean to show or hide the accordion)
        onChange => funtion (handler to change the showHide when is clicked)


    Task:   This component shows or Hide a content by clickin the button  

    To use the icons , this script must be loaded : <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
*/
import { ArrowDownCircle, ArrowUpCircle } from '../icons'
import './GesimaticAccordion.css'

export const GesimaticAccordion = ({showHide, title= '', openLabel = '', closedLabel = '', onChange, children}) => {
    
    const label = showHide ? openLabel : closedLabel
    const buttonClass = showHide ? 'gsmtc-accordion-button open' : 'gsmtc-accordion-button'

   return(
            <>
                <div className='gsmtc-accordion'>
                    <div className={buttonClass} onClick={() => onChange( ! showHide)}><h2 className='gsmtc-accordion-button-label'>{title}</h2><div className='gsmtc-accordion-action-button'><span style={{verticalAlign: 'middle'}}>{label}</span>{showHide &&  <ArrowUpCircle className={'bi'} name='upAccordion' onClick={() => onChange( ! showHide)}/>}{ ! showHide &&  <ArrowDownCircle className={'bi'} name='downAccordion' onClick={() => onChange( ! showHide)}/>} </div></div>
                        <div className='gsmtc-accordion-content' style={{display: (showHide)? 'block' : 'none'}}>
                            {children}
                        </div>                
                </div>            
            </>
    )
    
}