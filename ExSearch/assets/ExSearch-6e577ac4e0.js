console.log(
  "%c ExSearch %c https://blog.imalan.cn/archives/261/",
  "color: #fadfa3; background: #23b7e5; padding:5px;",
  "background: #1c2b36; padding:5px;"
);

$("body").append(
  `<div class="ins-search">
    <div class="ins-search-overlay"></div>
    <div class="ins-search-container">
      <div class="ins-input-wrapper">
        <input type="text" class="ins-search-input" placeholder="搜索点什么吧..." />
        <span class="ins-close ins-selectable">
          <i class="iconfont icon-close"></i>
        </span>
      </div>
      <div class="ins-section-wrapper">
        <div class="ins-section-container"></div>
      </div>
    </div>
  </div>`
);

(function (global) {
  global.INSIGHT_CONFIG = {
    TRANSLATION: {
      POSTS: "文章",
      PAGES: "页面",
      CATEGORIES: "分类",
      TAGS: "标签",
      UNTITLED: "（未命名）",
    },
    ROOT_URL: ExSearchConfig.root,
    CONTENT_URL: ExSearchConfig.api,
  };
})(window);

const ModalHelper = {
  openModal: () => document.body.classList.add("es-modal-open"),
  closeModal: () => document.body.classList.remove("es-modal-open"),
};

(function ($, config) {
  const $searchModal = $(".ins-search"),
    $searchInput = $searchModal.find(".ins-search-input"),
    $sectionContainer = $searchModal.find(".ins-section-container");

  function createSearchItem(icon, title, slug, preview, url) {
    const item = $(`<div class="ins-selectable ins-search-item">
                      <div class="header">
                        <i class="iconfont icon-${icon}"></i>
                        ${title || config.TRANSLATION.UNTITLED}
                        ${slug ? `<span class="ins-slug">${slug}</span>` : ""}
                      </div>
                      ${
                        preview
                          ? `<p class="ins-search-preview">${preview}</p>`
                          : ""
                      }
                    </div>`).attr("data-url", url);
    return item;
  }

  function handleInput() {
    const query = $searchInput.val();
    $.getJSON(config.CONTENT_URL, function (data) {
      $sectionContainer.empty();
      const results = processData(data, query);
      displayResults(results);
    });
  }

  function processData(data, query) {
    // Process and filter data based on the query
    // This is a simplified version, you might need to adjust it based on your actual data structure and requirements
    return data.filter((item) => item.title.includes(query));
  }

  function displayResults(results) {
    results.forEach((result) => {
      const item = createSearchItem(
        "file",
        result.title,
        null,
        result.preview,
        result.url
      );
      $sectionContainer.append(item);
    });
  }

  $searchInput.on("input", handleInput);

  $(document)
    .on("click focus", ".search-form-input", () => {
      $searchModal.addClass("show");
      ModalHelper.openModal();
      $searchInput.focus();
    })
    .on("click", ".ins-close, .ins-search-overlay", () => {
      $searchModal.removeClass("show");
      ModalHelper.closeModal();
    })
    .on("keydown", (e) => {
      if ($searchModal.hasClass("show")) {
        if (e.keyCode === 27) {
          // ESC to close
          $searchModal.removeClass("show");
          ModalHelper.closeModal();
        }
      }
    });
})(jQuery, window.INSIGHT_CONFIG);
