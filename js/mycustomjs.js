$ = jQuery;
$(document).ready(function () {
  const modelViewerColor = document.querySelector("model-viewer#model-popup");
  document.querySelector('#color-controls').addEventListener('click', (event) => {
    const colorString = event.target.dataset.color;
    if (!colorString) {
      return;
    }
    const color = colorString.split(',')
        .map(numberString => parseFloat(numberString));

    const [material] = modelViewerColor.model.materials;
    material.pbrMetallicRoughness.setBaseColorFactor(color);
  });
  
  const checkbox = document.querySelector("#neutral");
  checkbox.addEventListener("change", () => {
    modelViewerColor.environmentImage = checkbox.checked ? "neutral" : "";
  });
  self.setInterval(() => {
    modelViewerColor.animationName = modelViewerColor.animationName === 'Running' ?
      'Wave' : 'Running';
  }, 1500.0);

  $("#download-model").click(exportGLB);

  $("model-viewer.model-class").dblclick(function () {
    clickImage($(this).attr("src"),$(this).attr("data-id"));
    $('.myModel' ).addClass('open' );
  if ( $('.myModel' ).hasClass('open' ) ) {
  $('.container-fluid' ).addClass('blur' );
}
  });

  function clickImage(dataSrc,id) {
    var clickedModel = modelData.filter(list => list.id == id)
    var listData =  "<h4 class='title' id='model-name'>Name: "+clickedModel[0].name +"</h4>"+
                    "<span>Model Type : "+clickedModel[0].type +"</span>"+
                    "<p>Description : "+clickedModel[0].description +"</p>"
    $("div.right-content").append(listData)
    $(".myModel")
      .find("model-viewer")
      .attr("src", dataSrc);
     $(".myModel").fadeIn("slow");
  }
  

$('.closeIcon' ).click(function() {
    $('.myModel' ).removeClass('open' );
    $('.container-fluid' ).removeClass('blur' );
  });
  
  $(".closeIcon").click(function () {
    $("div.right-content").html("")
    $(".myModel").hide();
  });

});

async function exportGLB(){
    let modelNameHtml = document.getElementById("model-name").innerHTML;
    let modelName = modelNameHtml.substr(6);
    let modelViewer = document.getElementById("model-popup");
    const glTF = await modelViewer.exportScene();
    var file = new File([glTF], modelName.toLowerCase()+".glb");
    var link = document.createElement("a");
    link.download = file.name;
    link.href = URL.createObjectURL(file);
    link.click();
}