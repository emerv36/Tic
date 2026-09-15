function mensaje(titulo,text,icono) {
swal({
       closeOnClickOutside: false,
       closeOnEsc: false,
       allowOutsideClick: false,
       dangerMode: true,
       title: titulo,
       text: text,
       icon: icono,
       showClass: {
        popup: 'animate__animated animate__fadeInDown'
      },
      hideClass: {
        popup: 'animate__animated animate__fadeOutUp'
      }
        
   })

  
}   


