import React from "react";

// Componente para el icono arrow-down-circle
export const ArrowDownCircle = ({className, name, onClick}) => {

  const onPush = () => {
    onClick(name)
  }

  return (
    <div className={className} onClick={onPush}>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0M8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
      </svg>    
    </div>
  )
};

// Componente para el icono arrow-up-circle
export const ArrowUpCircle = ({className, name, onClick}) => {

  const onPush = () => {
    onClick(name)
  }

  return (
    <div className={className} onClick={onPush}>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8m15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
      </svg>    </div>
  )
};


// Componente para el icono caret_up_fill
export const CaretUpFill = ({className, name, onClick}) => {

    const onPush = () => {
      onClick(name)
    }

    return (
      <div className={className} onClick={onPush}>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
          <path d="m7.247 4.86-4.796 5.481c-.566.647-.106 1.659.753 1.659h9.592a1 1 0 0 0 .753-1.659l-4.796-5.48a1 1 0 0 0-1.506 0z"/>
        </svg>
      </div>
    )
  };
  
  // Componente para el icono caret_down_fill
  export const CaretDownFill = ({className, name, onClick}) => {

    const onPush = () => {
      onClick(name)
    }

  return (
          <span className={className} onClick={onPush}>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
              <path d="M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z"/>
            </svg>
          </span>
    )
  };
  
  // Exportar todos los iconos en un objeto
  export const icons = {
    arrow_down_circle : ArrowDownCircle,
    arrow_up_circle : ArrowUpCircle,
    caret_up_fill: CaretUpFill,
    caret_down_fill: CaretDownFill,
  };
