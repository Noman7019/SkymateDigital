// Web3Forms access key is a public client key (as used on the live site).
export const WEB3FORMS_KEY =
  import.meta.env.VITE_WEB3FORMS_KEY || "e5d09f0f-46b1-49d8-9b70-77842df23dbb";

export async function submitWeb3Forms(payload: Record<string, string>) {
  const res = await fetch("https://api.web3forms.com/submit", {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({ access_key: WEB3FORMS_KEY, from_name: "Sky Mate Digital Website", ...payload }),
  });
  const data = await res.json();
  if (!res.ok || !data.success) throw new Error(data.message || "Submission failed");
  return data;
}
