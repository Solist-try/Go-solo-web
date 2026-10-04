import type { SupabaseClient } from "@supabase/supabase-js";
import { mergeAdmin, type AdminState, type SiteContent } from "@/lib/admin/state";

export async function loadSiteContent(supabase: SupabaseClient): Promise<SiteContent | null> {
  const { data, error } = await supabase.from("site_content").select("document").eq("id", "site").maybeSingle();
  if (error || !data?.document || typeof data.document !== "object") return null;
  return mergeAdmin({ content: data.document as SiteContent }).content;
}

export async function loadDesk(supabase: SupabaseClient): Promise<AdminState | null> {
  const { data, error } = await supabase.from("admin_desk").select("document").eq("id", "desk").maybeSingle();
  if (error || !data?.document || typeof data.document !== "object") return null;
  return mergeAdmin(data.document as Partial<AdminState>);
}

export async function persistDesk(supabase: SupabaseClient, admin: AdminState) {
  const now = new Date().toISOString();
  await supabase.from("site_content").upsert({ id: "site", document: admin.content, updated_at: now });
  await supabase.from("admin_desk").upsert({ id: "desk", document: admin, updated_at: now });
}
