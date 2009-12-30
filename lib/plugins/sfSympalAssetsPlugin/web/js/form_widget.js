function addClearOnClick(tag, target) {
  tag.onclick = function() {
    target.value = '';
  }
}
function addViewOnClick(tag, target) {
  var href;
  tag.onclick = function() {
    href = target.value;
    if(href)
      window.open(href, 'sympal_assetsViewFile', '');
  }
}



HTMLElement.prototype.getElementByTagName = function(tag_name, index) {
  index = index ? index : 0;
  var tags = this.getElementsByTagName(tag_name);
  return tags[index];
}

HTMLElement.prototype.getElementsByClassName = function(class_name) {
  var tags = this.getElementsByTagName('*');
  var matches = [], i=0; tag;
  for(i; i<tags.length;++i) {
    tag = tags[i];
    if(tag.hasAttribute('class') && tag.getAttribute('class') == class_name)
      matches.push(tag);
  }
  return matches;
}

HTMLElement.prototype.getElementByClassName = function(class_name, index) {
  index = index ? index : 0;
  var tags = this.getElementsByClassName(class_name);
  return tags[index];
}

window.onload = function() {
  var wrappers = document.getElementsByClassName('sympal_assets_input_file');

  var wrapper, i=0, delete_link, view_lin, input;
  for(i; i<wrappers.length;++i) {
    wrapper = wrappers[i];
    input = wrapper.getElementByTagName('input');
    delete_link = wrapper.getElementByClassName('delete');
    view_link = wrapper.getElementByClassName('view');
    addClearOnClick(delete_link, input);
    addViewOnClick(view_link, input);
  }

}