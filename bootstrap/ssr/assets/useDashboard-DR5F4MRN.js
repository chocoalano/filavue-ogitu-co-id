import { u as useToast } from "./useToast-CTuSIf9f.js";
function useDashboard() {
  const toast = useToast();
  function digitsOnly(value) {
    return String(value ?? "").replace(/\D+/g, "");
  }
  function formatIDR(n) {
    return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(n);
  }
  function formatDate(d) {
    if (!d) return "—";
    try {
      return new Intl.DateTimeFormat("id-ID", { dateStyle: "medium" }).format(new Date(d));
    } catch {
      return d;
    }
  }
  function normalizePhoneDigits(value) {
    const digits = digitsOnly(value);
    if (digits === "") {
      return "";
    }
    if (digits.startsWith("62")) {
      return digits;
    }
    if (digits.startsWith("0")) {
      return `62${digits.slice(1)}`;
    }
    if (digits.startsWith("8")) {
      return `62${digits}`;
    }
    return `62${digits}`;
  }
  function extractPhoneLocalPart(value) {
    const normalized = normalizePhoneDigits(value);
    if (!normalized.startsWith("62")) {
      return normalized;
    }
    return normalized.slice(2);
  }
  function formatPhoneDisplay(value) {
    const localPart = extractPhoneLocalPart(value);
    if (localPart === "") {
      return "—";
    }
    return `+62 ${localPart}`;
  }
  async function copyToClipboard(text) {
    const username = text.trim();
    if (!username) {
      toast.add({
        title: "Username referral tidak tersedia",
        description: "Tidak ada username referral yang bisa disalin.",
        color: "warning"
      });
      return;
    }
    const referralUrl = `${window.location.origin}?username=${encodeURIComponent(username)}`;
    let copied = false;
    try {
      await window.navigator.clipboard.writeText(referralUrl);
      copied = true;
    } catch {
      const textarea = document.createElement("textarea");
      textarea.value = referralUrl;
      textarea.setAttribute("readonly", "");
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      copied = document.execCommand("copy");
      document.body.removeChild(textarea);
    }
    if (copied) {
      toast.add({
        title: "Link referral disalin",
        description: "Tautan pendaftaran berhasil disalin ke clipboard.",
        color: "success"
      });
      return;
    }
    toast.add({
      title: "Gagal menyalin link",
      description: "Coba lagi dalam beberapa saat.",
      color: "error"
    });
  }
  return {
    digitsOnly,
    formatIDR,
    formatDate,
    formatPhoneDisplay,
    extractPhoneLocalPart,
    copyToClipboard
  };
}
export {
  useDashboard as u
};
