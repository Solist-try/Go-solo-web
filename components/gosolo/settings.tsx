"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { toast } from "sonner";
import { ChoiceGrid, Frame, PageIntro, QuietSwitch, fieldClass, areaClass, pill } from "@/components/gosolo/pieces";
import { NextStep } from "@/components/gosolo/next-step";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Textarea } from "@/components/ui/textarea";
import { useGoSolo } from "@/lib/gosolo";
import { INTERESTS } from "@/lib/types";

export function SettingsScreen() {
  const { user, mode, updateProfile, setInterests, resetPassword, deleteAccount, logout } = useGoSolo();
  const router = useRouter();
  const [name, setName] = useState(user?.displayName ?? "");
  const [bio, setBio] = useState(user?.bio ?? "");
  const [location, setLocation] = useState(user?.location ?? "");
  const [interests, setInterestList] = useState(user?.interests ?? []);
  const [photo, setPhoto] = useState<File | null>(null);
  const [password, setPassword] = useState("");
  const [confirmName, setConfirmName] = useState("");
  const [open, setOpen] = useState(false);
  const [error, setError] = useState("");

  if (!user) return null;

  async function saveProfile() {
    const result = await updateProfile(
      { displayName: name.trim(), bio: bio.trim(), location: location.trim() },
      photo,
    );
    const interestsResult = await setInterests(interests);
    if (result.error || interestsResult.error) {
      setError(result.error || interestsResult.error || "Could not save.");
      return;
    }
    setError("");
    toast("Saved. Your profile is up to date.");
  }

  return (
    <Frame>
      <PageIntro title="Settings">
        A few quiet controls. Go Solo will not try to become louder.
      </PageIntro>
      {mode === "demo" ? (
        <p className="mt-4 text-sm text-ink-soft">Preview on this browser. Connect Supabase to keep accounts on a server.</p>
      ) : null}
      <Tabs defaultValue="profile" className="mt-10 gap-6">
        <TabsList className="h-auto w-full flex-wrap justify-start gap-2 bg-transparent p-0">
          {[
            ["profile", "Profile"],
            ["privacy", "Privacy"],
            ["notifications", "Notifications"],
            ["account", "Account settings"],
            ["delete", "Delete account"],
          ].map(([value, label]) => (
            <TabsTrigger
              key={value}
              value={value}
              className="h-11 rounded-full px-4 data-active:bg-ink data-active:text-background"
            >
              {label}
            </TabsTrigger>
          ))}
        </TabsList>
        <TabsContent value="profile" className="space-y-5">
          <label className="block space-y-2">
            <span className="text-sm">Display name</span>
            <Input className={fieldClass} value={name} maxLength={40} onChange={(event) => setName(event.target.value)} />
          </label>
          <label className="block space-y-2">
            <span className="text-sm">Bio</span>
            <Textarea className={areaClass} value={bio} maxLength={600} onChange={(event) => setBio(event.target.value)} />
          </label>
          <label className="block space-y-2">
            <span className="text-sm">Location</span>
            <Input className={fieldClass} value={location} maxLength={80} onChange={(event) => setLocation(event.target.value)} />
          </label>
          <label className="block space-y-2">
            <span className="text-sm">Profile photo</span>
            <Input className={fieldClass} type="file" accept="image/*" onChange={(event) => setPhoto(event.target.files?.[0] ?? null)} />
          </label>
          <ChoiceGrid label="Interests" options={INTERESTS} value={interests} onChange={setInterestList} />
          {error ? <p className="text-sm text-destructive">{error}</p> : null}
          <Button className={pill} onClick={() => void saveProfile()}>
            Save profile
          </Button>
        </TabsContent>
        <TabsContent value="privacy" className="space-y-3">
          <QuietSwitch
            label="Show location on your profile"
            hint="People at your waypoints can see the place you named."
            checked={user.showLocation}
            onChange={(showLocation) => void updateProfile({ showLocation })}
          />
          <QuietSwitch
            label="Show waypoints on your profile"
            hint="Your waypoints stay visible to you either way."
            checked={user.showWaypoints}
            onChange={(showWaypoints) => void updateProfile({ showWaypoints })}
          />
        </TabsContent>
        <TabsContent value="notifications" className="space-y-3">
          <p className="max-w-xl text-ink-soft">
            We only write when it helps you take a next step. No streaks, no counts, no nudges for their own sake.
          </p>
          <QuietSwitch
            label="Replies to something you shared"
            checked={user.notifyReplies}
            onChange={(notifyReplies) => void updateProfile({ notifyReplies })}
          />
          <QuietSwitch
            label="A weekly note if a seed has been quiet"
            hint="At most one gentle reminder, and only after a week."
            checked={user.notifyCheckins}
            onChange={(notifyCheckins) => void updateProfile({ notifyCheckins })}
          />
          <QuietSwitch
            label="Waypoint welcomes"
            hint="Off by default. The waypoint itself is the welcome."
            checked={user.notifyWaypoints}
            onChange={(notifyWaypoints) => void updateProfile({ notifyWaypoints })}
          />
        </TabsContent>
        <TabsContent value="account" className="space-y-5">
          <p>
            <span className="text-sm text-ink-soft">Email</span>
            <span className="mt-1 block text-lg">{user.email}</span>
          </p>
          <label className="block space-y-2">
            <span className="text-sm">New password</span>
            <Input
              className={fieldClass}
              type="password"
              autoComplete="new-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
            />
          </label>
          <div className="flex flex-wrap gap-3">
            <Button
              className={pill}
              onClick={async () => {
                const result = await resetPassword(password);
                if (result.error) toast(result.error);
                else {
                  setPassword("");
                  toast("Password updated.");
                }
              }}
            >
              Update password
            </Button>
            <Button
              variant="outline"
              className={`${pill} bg-transparent`}
              onClick={() => {
                void logout();
                router.push("/");
              }}
            >
              Log out
            </Button>
          </div>
        </TabsContent>
        <TabsContent value="delete">
          <p className="max-w-xl text-lg leading-relaxed">
            Deleting your account removes your profile, seeds, stories, and campfire words. This cannot be undone.
          </p>
          <Button variant="destructive" className={`${pill} mt-6`} onClick={() => setOpen(true)}>
            Delete account
          </Button>
          <Dialog open={open} onOpenChange={setOpen}>
            <DialogContent className="rounded-[28px] bg-background sm:max-w-md">
              <DialogHeader>
                <DialogTitle className="font-serif text-3xl">Delete your account?</DialogTitle>
                <DialogDescription>
                  Type your display name to confirm. The room will still be here if you return someday as someone new.
                </DialogDescription>
              </DialogHeader>
              <label className="block space-y-2">
                <span className="text-sm">Display name</span>
                <Input className={fieldClass} value={confirmName} onChange={(event) => setConfirmName(event.target.value)} />
              </label>
              <Button
                variant="destructive"
                className={pill}
                disabled={confirmName.trim() !== user.displayName}
                onClick={async () => {
                  const result = await deleteAccount();
                  if (result.error) {
                    toast(result.error);
                    return;
                  }
                  router.push("/");
                }}
              >
                Delete forever
              </Button>
            </DialogContent>
          </Dialog>
        </TabsContent>
      </Tabs>
      <NextStep />
    </Frame>
  );
}
