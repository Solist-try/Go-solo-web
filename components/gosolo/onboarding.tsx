"use client";

import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import { ChoiceGrid, Frame, PageIntro, Panel, fieldClass, areaClass, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useGoSolo } from "@/lib/gosolo";
import { INTENTIONS, INTERESTS } from "@/lib/types";

const steps = ["Account", "What brings you", "Profile", "Interests", "Waypoints"];

export function OnboardingFlow() {
  const { ready, user } = useGoSolo();
  const router = useRouter();

  useEffect(() => {
    if (!ready) return;
    if (!user) {
      router.replace("/register");
      return;
    }
    if (!user.emailVerified) {
      router.replace("/verify-email");
      return;
    }
    if (user.onboardingComplete) router.replace("/dashboard");
  }, [ready, user, router]);

  if (!ready || !user || !user.emailVerified || user.onboardingComplete) return null;
  return <OnboardingForm user={user} />;
}

function OnboardingForm({ user }: { user: NonNullable<ReturnType<typeof useGoSolo>["user"]> }) {
  const { world, waypoints: catalogWaypoints, updateProfile, setInterests, joinWaypoint, leaveWaypoint, completeOnboarding } = useGoSolo();
  const router = useRouter();
  const [step, setStep] = useState(2);
  const [intentions, setIntentions] = useState(user.intentions);
  const [name, setName] = useState(user.displayName);
  const [bio, setBio] = useState(user.bio);
  const [location, setLocation] = useState(user.location);
  const [photo, setPhoto] = useState<File | null>(null);
  const [interests, setInterestList] = useState(user.interests);
  const [waypoints, setWaypoints] = useState(
    world.memberships.filter((item) => item.userId === user.id).map((item) => item.waypointId),
  );
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);

  async function continueFlow() {
    if (!user) return;
    setError("");
    setPending(true);
    if (step === 2) {
      if (intentions.length === 0) {
        setError("Choose at least one. You can change it later.");
        setPending(false);
        return;
      }
      const result = await updateProfile({ intentions });
      setPending(false);
      if (result.error) {
        setError(result.error);
        return;
      }
      setStep(3);
      return;
    }
    if (step === 3) {
      if (!name.trim()) {
        setError("A display name helps people sit with you.");
        setPending(false);
        return;
      }
      const result = await updateProfile(
        { displayName: name.trim(), bio: bio.trim(), location: location.trim() },
        photo,
      );
      setPending(false);
      if (result.error) {
        setError(result.error);
        return;
      }
      setStep(4);
      return;
    }
    if (step === 4) {
      if (interests.length === 0) {
        setError("Choose at least one interest.");
        setPending(false);
        return;
      }
      const result = await setInterests(interests);
      setPending(false);
      if (result.error) {
        setError(result.error);
        return;
      }
      setStep(5);
      return;
    }
    if (waypoints.length === 0) {
      setError("Choose one waypoint. You can leave it later.");
      setPending(false);
      return;
    }
    const current = world.memberships.filter((item) => item.userId === user.id).map((item) => item.waypointId);
    for (const id of waypoints) {
      if (!current.includes(id)) await joinWaypoint(id);
    }
    for (const id of current) {
      if (!waypoints.includes(id)) await leaveWaypoint(id);
    }
    await completeOnboarding();
    setPending(false);
    router.push("/dashboard");
  }

  return (
    <Frame>
      <PageIntro eyebrow={`Step ${step} of 5`} title={steps[step - 1] ?? "Welcome"}>
        {step === 2
          ? "What brings you here? Choose as many as are true."
          : step === 3
            ? "A name, a few sentences, and a place if you want one. A photo is optional."
            : step === 4
              ? "Interests are doors, not a brand."
              : "Waypoints are where people exploring a similar stretch of life gather."}
      </PageIntro>
      <ol className="mt-8 flex flex-wrap gap-2" aria-label="Onboarding progress">
        {steps.map((label, index) => {
          const number = index + 1;
          const state = number < step ? "Done" : number === step ? "Now" : "Later";
          return (
            <li
              key={label}
              className={`rounded-full px-4 py-2 text-sm ${number === step ? "bg-ink text-background" : "bg-white/70 text-ink-soft"}`}
            >
              <span className="sr-only">{state}: </span>
              {label}
            </li>
          );
        })}
      </ol>
      <Panel className="mt-8">
        {step === 2 ? (
          <ChoiceGrid label="What brings you here" options={INTENTIONS} value={intentions} onChange={setIntentions} />
        ) : null}
        {step === 3 ? (
          <div className="space-y-5">
            <label className="block space-y-2">
              <span className="text-sm">Display name</span>
              <Input className={fieldClass} value={name} onChange={(event) => setName(event.target.value)} maxLength={40} required />
            </label>
            <label className="block space-y-2">
              <span className="text-sm">Bio</span>
              <Textarea
                className={areaClass}
                value={bio}
                maxLength={600}
                onChange={(event) => setBio(event.target.value)}
                placeholder="A few true sentences. Where you are in life is enough."
              />
            </label>
            <label className="block space-y-2">
              <span className="text-sm">Location, optional</span>
              <Input className={fieldClass} value={location} onChange={(event) => setLocation(event.target.value)} maxLength={80} />
            </label>
            <label className="block space-y-2">
              <span className="text-sm">Profile photo, optional</span>
              <Input
                className={fieldClass}
                type="file"
                accept="image/*"
                onChange={(event) => setPhoto(event.target.files?.[0] ?? null)}
              />
            </label>
          </div>
        ) : null}
        {step === 4 ? (
          <ChoiceGrid label="Interests" options={INTERESTS} value={interests} onChange={setInterestList} />
        ) : null}
        {step === 5 ? (
          <ChoiceGrid
            label="Waypoints"
            options={catalogWaypoints.map((waypoint) => ({
              id: waypoint.id,
              label: waypoint.name,
              hint: waypoint.summary,
            }))}
            value={waypoints}
            onChange={setWaypoints}
          />
        ) : null}
        {error ? (
          <p role="alert" className="mt-5 text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <div className="mt-8 flex flex-wrap gap-3">
          {step > 2 ? (
            <Button type="button" variant="outline" className={`${pill} bg-transparent`} onClick={() => setStep((value) => value - 1)}>
              Back
            </Button>
          ) : null}
          <Button type="button" className={pill} disabled={pending} onClick={() => void continueFlow()}>
            {pending ? "Saving…" : step === 5 ? "Enter Go Solo" : "Continue"}
          </Button>
        </div>
      </Panel>
    </Frame>
  );
}
