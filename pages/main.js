let data = [];
let currentPage = 1;
const apiPath = '../products.php';
const colName = [
  'pid',
  'title',
  'tag',
  'classify',
  'unit',
  'price',
  'feature',
  'sTime',
  'img'
];
function readURL(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      $('#preview_img').attr('src', e.target.result);
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function setModal(info) {
  let {
    pid = null,
    title = '',
    tag = '',
    classify = '',
    unit = '',
    price = '',
    sTime = '',
    feature = '',
    img = ''
  } = info;
  const modalTitle = document.querySelector('#exampleModal .modal-title');
  const form = document.querySelector('#productform');
  modalTitle.innerHTML = pid ? '編輯' : '新增';
  form.innerHTML = `
    <label for="title">title:</label>
    <input type="text" id="title" value="${title}" name="title" maxlength="20">
    <br />
    <p>tag:</p>
    <input type="radio" name="tag" id="t1" value="紅茶">
    <label for="t1">紅茶</label>
    <input type="radio" name="tag" id="t2" value="綠茶">
    <label for="t2">綠茶</label>
    <input type="radio" name="tag" id="t3" value="烏龍茶">
    <label for="t3">烏龍茶</label>
    <input type="radio" name="tag" id="t4" value="東方美人茶">
    <label for="t4">東方美人茶</label>
    <input type="radio" name="tag" id="t5" value="金萱/翠玉">
    <label for="t5">金萱 / 翠玉</label>
    <input type="radio" name="tag" id="t6" value="鐵觀音">
    <label for="t6">鐵觀音</label>
    <input type="radio" name="tag" id="t7" value="四季春/文山包種">
    <label for="t7">四季春 / 文山包種</label>
    <input type="radio" name="tag" id="t8" value="其他">
    <label for="t8">其他</label>
    <br />
    <label for="classify">classify:</label>
    <input type="text" name="classify" value="${classify}">
    <br />
    <label for="unit">unit:</label>
    <input type="text" name="unit" value="${unit}">
    <br />
    <label for="price">price:</label>
    <input type="number" name="price" max="1000000" value="${price}">
    <br />
    <label for="sTime">sTime:</label>
    <input type="number" name="sTime" max="10" value="${sTime}">
    <br />
    <label for="feature">features:</label>
    <input type="text" name="feature" maxlength="1000" value="${feature}">
    <br />
    <label for="img">img:</label>
    <input type="file" name="img" id="img">
    <img id="preview_img" src="${
      img ? '../images/' + img : ''
    }" alt="" width="200">
    <br />
  `;
  const tags = document.querySelectorAll('#productform [name=tag]');
  const submit = document.querySelector('#submit');
  if (pid) {
    submit.dataset.pid = pid;
  } else {
    submit.dataset.pid = '';
  }
  for (const radio of tags) {
    if (radio.value === tag) {
      radio.setAttribute('checked', true);
    }
  }
}
function callMsg(success, msg) {
  let alert = null
  if (success) {
    alert = $('#msg-success')
  } else {
    alert = $('#msg-error')
  }
  alert.html(msg).css('opacity', 100)
  setTimeout(() => {
    alert.css('opacity', 0)
  }, 2000);
}
function renderPagination(currentPage, totalPage) {
  let pagination = '';
  for (let i = 0; i < totalPage; i++) {
    let page = i + 1;
    pagination += `
      <li class="page-item ${page === currentPage ? 'active' : ''}">
        <a class="page-link" href="#" data-page="${page}">${page}</a>
      </li>
    `;
  }
  $('#page-navigation .pagination').html(pagination);
}
function renderTable() {
  table = '';
  table += `
    <table>
      <thead>
        <tr>
          <th>pid</th>
          <th>title</th>
          <th>tag</th>
          <th>classify</th>
          <th>unit</th>
          <th>price</th>
          <th>feature</th>
          <th>sTime</th>
          <th>img</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
  `;
  for (const el of data) {
    table += `
      <tr>
        <td><input type="checkbox" name="batch" id="${el.pid}"></td>
        <td>${el.title}</td>
        <td>${el.tag}</td>
        <td>${el.classify}</td>
        <td>${el.unit}</td>
        <td>${el.price}</td>
        <td>${el.feature}</td>
        <td>${el.sTime}</td>
        <td><img src="../images/${el.img}" alt="" width="100"></td>
        <td><button data-pid="${el.pid}" data-toggle="modal" data-target="#exampleModal">編輯</button></td>
        <td><button class="delete"" data-pid="${el.pid}">刪除</button></td>
      </tr>
    `;
  }
  table += '</tbody></table>';
  $('#list').html(table);
}

function getData(page) {
  if (page === 'all') {
    $.ajax({
      url: apiPath + '?page=all',
      type: 'GET',
      success: function(d) {
        res = JSON.parse(d);
        if (res.success) {
          if (res.success) {
            data = res.data;
          }
          currentPage = res.currentPage;
          renderTable();
          renderPagination(res.currentPage, res.totalPage);
        } else {
          callMsg(false, res.msg)
        }
      }
    });
  } else {
    $.ajax({
      url: apiPath + `?page=${page}`,
      type: 'GET',
      success: function(d) {
        res = JSON.parse(d);
        if (res.success) {
          if (res.success) {
            data = res.data;
          }
          currentPage = res.currentPage;
          renderTable();
          renderPagination(res.currentPage, res.totalPage);
        } else {
          callMsg(false, res.msg)
        }
      }
    });
  }
}

$(document).ready(function() {
  getData(currentPage);
  // 圖片預覽
  $(document).on('change', 'input#img', e => {
    readURL(e.target);
  });
  // modal 內容
  $(document).on('click', '[data-toggle=modal]', e => {
    const info = data.find(el => el.pid === e.target.dataset.pid) || {};
    setModal(info);
  });
  // 取的商品列表
  $(document).on('click', '#page-navigation .page-link', e => {
    let page = e.target.dataset.page || currentPage;
    getData(page);
  });
  // 新增 or 修改
  $(document).on('click', '#submit', e => {
    e.preventDefault();
    let f = document.querySelector('#productform');
    let formData = new FormData(f);
    if (e.target.dataset.pid) {
      formData.append('pid', e.target.dataset.pid);
    }
    $.ajax({
      url: apiPath,
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(d) {
        res = JSON.parse(d);
        callMsg(res.success, res.msg)
        getData(currentPage);
      }
    });
  });
  // 刪除
  $(document).on('click', 'button.delete', e => {
    let pid = e.target.dataset.pid;
    $.ajax({
      url: apiPath,
      method: 'DELETE',
      data: `[${pid}]`,
      contentType: false,
      processData: false,
      success: function(d) {
        res = JSON.parse(d);
        callMsg(res.success, res.msg)
        getData(currentPage);
      }
    });
  });
  // 批量刪除
  $(document).on('click', '#deletes', e => {
    let ids = [];
    $('[name=batch]').each((i, el) => {
      if (el.checked) {
        ids.push(el.id);
      }
    });
    $.ajax({
      url: apiPath,
      method: 'DELETE',
      data: JSON.stringify(ids),
      contentType: false,
      processData: false,
      success: function(d) {
        res = JSON.parse(d);
        callMsg(res.success, res.msg)
        if (!res.success) {
          alert(res.msg);
        }
        getData(currentPage);
      }
    });
  });
});
