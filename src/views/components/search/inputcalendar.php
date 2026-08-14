<div class=" d-flex flex-wrap flex-lg-row flex-lg-nowrap justify-content-center align-items-center gap-3">
    <div class="inputCalendar d-flex align-items-center  mt-3 p-2 rounded-3 shadow-sm position-relative">

        <label for="fecha" class="cal-label position-absolute ">Fecha salida</label>

        <input 
            type="date"  
            class="form-control form-control-lg custom-cal-in " 
            id="fecha" 
            name="fecha" 
            placeholder="Fecha"
            min="<?php echo date('Y-m-d'); ?>"
        >
        <p id="fecha-sub" class="fecha-sub position-absolute "></p>
    </div>

    <div class="inputCalendar inputCalendar-bloqueado d-flex align-items-center  mt-3 p-2 rounded-3 shadow-sm position-relative" id="calendar-vuelta">

        <label for="fecha-vuelta" class="cal-label position-absolute ">Fecha vuelta</label>

        <input 
            type="date"  
            class="form-control form-control-lg custom-cal-in " 
            id="fecha-vuelta" 
            name="fecha-vuelta" 
            placeholder="Fecha de vuelta"
            min="<?php echo date('Y-m-d'); ?>"
        >
        <p id="fecha-vuelta-sub" class="fecha-sub position-absolute "></p>
    </div>
</div>