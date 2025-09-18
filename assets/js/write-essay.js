// Write Essay JavaScript Functions
let timer;
let timeLeft;
let wordCount = 0;
let hasSubmitted = false;

$(document).ready(function () {
  // Initialize
  initializeWriteEssay();

  // Event listeners
  $("#essayContent").on("input", updateWordCount);
  $("#saveBtn").on("click", saveDraft);
  $("#essayForm").on("submit", submitEssay);

  // Auto-save every 30 seconds
  setInterval(autoSave, 30000);

  // Warn before leaving page if content exists
  window.addEventListener("beforeunload", function (e) {
    if (!hasSubmitted && $("#essayContent").val().trim().length > 0) {
      e.preventDefault();
      e.returnValue = "";
      return "";
    }
  });
});

function initializeWriteEssay() {
  // Initialize word count
  updateWordCount();

  // Initialize timer
  const timeElement = $("#timeRemaining");
  if (timeElement.length) {
    const timeText = timeElement.text();
    const minutes = parseInt(timeText.split(":")[0]);
    timeLeft = minutes * 60; // Convert to seconds

    // Only start timer if not submitted
    if (!$("#submitBtn").is(":disabled")) {
      startTimer();
    }
  }

  // Load saved draft
  loadDraft();

  // Check if already submitted
  hasSubmitted = $("#submitBtn").is(":disabled");
}

function updateWordCount() {
  const content = $("#essayContent").val();
  const words = content
    .trim()
    .split(/\s+/)
    .filter((word) => word.length > 0);
  wordCount = words.length;

  $("#currentWordCount").text(wordCount);

  // Update word count color based on requirement
  const requiredWords = parseInt(
    $("#currentWordCount").parent().text().split("/")[1].trim().split(" ")[0]
  );
  const wordCountElement = $("#currentWordCount");

  if (wordCount < requiredWords * 0.8) {
    wordCountElement.css("color", "#dc3545"); // Red
  } else if (wordCount < requiredWords) {
    wordCountElement.css("color", "#ffc107"); // Yellow
  } else {
    wordCountElement.css("color", "#28a745"); // Green
  }
}

function startTimer() {
  timer = setInterval(function () {
    timeLeft--;

    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;

    $("#timeRemaining").text(
      String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0")
    );

    // Change color when time is running out
    if (timeLeft <= 300) {
      // 5 minutes
      $("#timeRemaining").css("color", "#dc3545");
      $("#timeRemaining").addClass("time-warning");
    } else if (timeLeft <= 600) {
      // 10 minutes
      $("#timeRemaining").css("color", "#ffc107");
    }

    // Auto submit when time is up
    if (timeLeft <= 0) {
      clearInterval(timer);
      autoSubmit();
    }
  }, 1000);
}

function saveDraft() {
  const content = $("#essayContent").val();
  const promptId = $('input[name="prompt_id"]').val();

  if (!content.trim()) {
    showAlert("Vui lòng nhập nội dung trước khi lưu nháp!", "warning");
    return;
  }

  // Show loading
  const saveBtn = $("#saveBtn");
  const originalText = saveBtn.html();
  saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang lưu...');
  saveBtn.prop("disabled", true);

  $.ajax({
    url: "/webhocngoaingu/api/save-draft.php",
    method: "POST",
    data: {
      prompt_id: promptId,
      content: content,
      word_count: wordCount,
    },
    success: function (response) {
      if (response.success) {
        showAlert("Đã lưu nháp thành công!", "success");
        // Store in localStorage as backup
        localStorage.setItem("draft_" + promptId, content);
      } else {
        showAlert(response.message || "Có lỗi xảy ra khi lưu nháp!", "error");
      }
    },
    error: function () {
      showAlert("Không thể kết nối đến server!", "error");
      // Save to localStorage as fallback
      localStorage.setItem("draft_" + promptId, content);
      showAlert("Đã lưu nháp tạm thời trên máy!", "info");
    },
    complete: function () {
      saveBtn.html(originalText);
      saveBtn.prop("disabled", false);
    },
  });
}

function loadDraft() {
  const promptId = $('input[name="prompt_id"]').val();

  // Try to load from localStorage first
  const localDraft = localStorage.getItem("draft_" + promptId);
  if (localDraft && !$("#essayContent").val()) {
    $("#essayContent").val(localDraft);
    updateWordCount();
    showAlert("Đã khôi phục bản nháp!", "info");
  }
}

function autoSave() {
  const content = $("#essayContent").val();
  if (content.trim() && !hasSubmitted) {
    const promptId = $('input[name="prompt_id"]').val();
    localStorage.setItem("draft_" + promptId, content);
  }
}

function submitEssay(e) {
  e.preventDefault();

  const content = $("#essayContent").val();
  const requiredWords = parseInt(
    $("#currentWordCount").parent().text().split("/")[1].trim().split(" ")[0]
  );

  if (!content.trim()) {
    showAlert("Vui lòng nhập nội dung bài viết!", "warning");
    return;
  }

  if (wordCount < requiredWords * 0.8) {
    if (
      !confirm(
        `Bài viết của bạn chỉ có ${wordCount} từ, ít hơn yêu cầu (${requiredWords} từ). Bạn có chắc chắn muốn nộp bài?`
      )
    ) {
      return;
    }
  }

  // Show loading
  const submitBtn = $("#submitBtn");
  const originalText = submitBtn.html();
  submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Đang nộp...');
  submitBtn.prop("disabled", true);

  const formData = {
    prompt_id: $('input[name="prompt_id"]').val(),
    ma_khoa_hoc: $('input[name="ma_khoa_hoc"]').val(),
    content: content,
    word_count: wordCount,
    time_spent: getTimeSpent(),
  };

  $.ajax({
    url: "/webhocngoaingu/api/submit-essay-new.php",
    method: "POST",
    data: formData,
    dataType: "json",
    xhrFields: {
      withCredentials: true,
    },
    success: function (response) {
      console.log("Submit response:", response);
      if (response.success) {
        hasSubmitted = true;
        clearInterval(timer);

        // Clear draft
        const promptId = $('input[name="prompt_id"]').val();
        localStorage.removeItem("draft_" + promptId);

        showAlert(
          "Nộp bài thành công! Đang chuyển về danh sách bài viết...",
          "success"
        );

        // Disable form
        $("#essayContent").prop("readonly", true);
        submitBtn.html('<i class="fas fa-check"></i> Đã nộp bài');
        $("#saveBtn").hide();

        // Redirect after 2 seconds with correct parameters
        setTimeout(function () {
          const maKhoaHoc = $('input[name="ma_khoa_hoc"]').val();
          const maChuDe = $('input[name="ma_chu_de"]').val();

          console.log("Redirecting with:", { maKhoaHoc, maChuDe });

          // Redirect to writing list with correct topic
          window.location.href = `/webhocngoaingu/public/client/writing.php?maKhoaHoc=${maKhoaHoc}&maBaiHoc=${maChuDe}`;
        }, 2000);
      } else {
        showAlert(response.message || "Có lỗi xảy ra khi nộp bài!", "error");
        submitBtn.html(originalText);
        submitBtn.prop("disabled", false);
      }
    },
    error: function (xhr, status, error) {
      console.error("Submit error:", { xhr, status, error });
      console.error("Response text:", xhr.responseText);
      showAlert("Không thể kết nối đến server! Chi tiết: " + error, "error");
      submitBtn.html(originalText);
      submitBtn.prop("disabled", false);
    },
  });
}

function autoSubmit() {
  if (!hasSubmitted) {
    showAlert("Hết thời gian! Bài viết sẽ được nộp tự động.", "warning");
    setTimeout(function () {
      $("#essayForm").submit();
    }, 2000);
  }
}

function getTimeSpent() {
  const timeElement = $("#timeRemaining").text();
  const totalMinutes = parseInt(
    $('input[name="prompt_id"]').closest(".time-limit").text().split(" ")[1]
  );
  const remainingTime = timeElement.split(":");
  const remainingMinutes = parseInt(remainingTime[0]);
  const remainingSeconds = parseInt(remainingTime[1]);

  const totalSeconds = totalMinutes * 60;
  const remainingTotalSeconds = remainingMinutes * 60 + remainingSeconds;
  const spentSeconds = totalSeconds - remainingTotalSeconds;

  return Math.max(0, spentSeconds);
}

function showAlert(message, type) {
  // Remove existing alerts
  $(".custom-alert").remove();

  const alertClass =
    type === "success"
      ? "alert-success"
      : type === "warning"
      ? "alert-warning"
      : type === "error"
      ? "alert-danger"
      : "alert-info";

  const icon =
    type === "success"
      ? "fas fa-check-circle"
      : type === "warning"
      ? "fas fa-exclamation-triangle"
      : type === "error"
      ? "fas fa-times-circle"
      : "fas fa-info-circle";

  const alert = $(`
        <div class="alert ${alertClass} custom-alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
            <i class="${icon}"></i>
            ${message}
            <button type="button" class="btn-close" style="margin-left: 10px;" onclick="$(this).closest('.alert').remove()">×</button>
        </div>
    `);

  $("body").append(alert);

  // Auto remove after 5 seconds
  setTimeout(function () {
    alert.fadeOut(function () {
      $(this).remove();
    });
  }, 5000);
}

// Add CSS for time warning animation
$("<style>")
  .text(
    `
    .time-warning {
        animation: pulse 1s infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .custom-alert .btn-close {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
    }
`
  )
  .appendTo("head");
